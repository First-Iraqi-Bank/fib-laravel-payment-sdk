<?php

    namespace FirstIraqiBank\FIBPaymentSDK\Services;

    use Exception;
    use FirstIraqiBank\FIBPaymentSDK\Model\FibPayment;
    use FirstIraqiBank\FIBPaymentSDK\Model\FibRefund;
    use FirstIraqiBank\FIBPaymentSDK\Services\Contracts\FIBPaymentIntegrationServiceInterface;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;

    class FIBPaymentIntegrationService implements FIBPaymentIntegrationServiceInterface
    {
        protected $baseUrl;

        // Declare properties
        protected $fibPaymentRepository;
        protected $fibAuthIntegrationService;

        public function __construct(
            FIBPaymentRepositoryService $fibPaymentRepository,
            FIBAuthIntegrationService $fibAuthIntegrationService
        )
        {
            // Assign properties
            $this->fibPaymentRepository = $fibPaymentRepository;
            $this->fibAuthIntegrationService = $fibAuthIntegrationService;
            $this->baseUrl = config('fib.base_url');
        }

        /**
         * @throws Exception
         */
        private function postRequest($url, array $data)
        {
            $token = $this->fibAuthIntegrationService->getToken();
            $response = retry(3, function () use ($url, $data, $token) {
                return Http::withOptions([
                    'verify' => false, // Disable SSL verification
                ])->asJson()
                    ->withToken($token)
                    ->post($url, $data);
            }, 100);

            if (!$response->successful()) {
                Log::error('Failed to post request to FIB Payment API.', [
                    'url' => $url,
                    'data' => $data,
                    'response' => $response->body(),
                ]);
                throw new Exception('Failed to post request.');
            }
            return $response;
        }

        /**
         * @throws Exception
         */
        private function getRequest($url)
        {
            $token = $this->fibAuthIntegrationService->getToken();
            $response = retry(3, function () use ($url, $token) {
                return Http::withOptions([
                    'verify' => false, // Disable SSL verification
                ])->withToken($token)
                    ->get($url);
            }, 100);

            if (!$response->successful()) {
                Log::error('Failed to get request from FIB Payment API.', [
                    'url' => $url,
                    'response' => $response->body(),
                ]);
                throw new Exception('Failed to get request.');
            }

            return $response->json();
        }

        /**
         * @throws Exception
         */
        public function createPayment($amount, $callback = null, $description = null)
        {
            $data = $this->getPaymentData($amount, $callback, $description);
            $paymentData = $this->postRequest("{$this->baseUrl}/payments", $data);
            return $this->fibPaymentRepository->createPayment($paymentData->json(), $amount);
        }

        /**
         * @throws Exception
         */
        public function checkPaymentStatus($paymentId)
        {
            $status = $this->getRequest("{$this->baseUrl}/payments/{$paymentId}/status")['status'];
            $this->fibPaymentRepository->updatePaymentStatus($paymentId, $status);
            return $status;
        }

        public function handleCallback($paymentId, $status)
        {
            $this->fibPaymentRepository->updatePaymentStatus($paymentId, $status);
        }

        public function getPaymentData($amount, $callback = null, $description = null)
        {
            return [
                'monetaryValue' => [
                    'amount' => $amount,
                    'currency' => config('fib.currency'),
                ],
                'statusCallbackUrl' => $callback ?? config('fib.callback'),
                'description' => $description ?? '',
                'refundableFor' => config('fib.refundable_for'),
            ];
        }

        public function refund($paymentId)
        {
            $response = $this->postRequest("{$this->baseUrl}/payments/{$paymentId}/refund", []);
            if ($response->status() == 202) {
                $refundData = [
                    'status' => FibRefund::SUCCESS,
                ];
                $this->fibPaymentRepository->updateOrCreateRefund($paymentId, $refundData);
                $this->fibPaymentRepository->updatePaymentStatus($paymentId, FibPayment::REFUNDED);
            } else {
                $refundData = [
                    'fib_trace_id' => $response['traceId'],
                    'refund_failure_reason' => implode(', ', array_column($response['errors'], 'code')),
                    'status' => FibRefund::FAILED,
                ];
                $this->fibPaymentRepository->updateOrCreateRefund($paymentId, $refundData);
            }
        }
    }
