<?php

namespace FirstIraqiBank\FIBPaymentSDK\Services;

use Exception;
use FirstIraqiBank\FIBPaymentSDK\Model\FibPayment;
use FirstIraqiBank\FIBPaymentSDK\Model\FibRefund;
use FirstIraqiBank\FIBPaymentSDK\Services\Contracts\FIBPaymentIntegrationServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FIBPaymentIntegrationService implements FIBPaymentIntegrationServiceInterface
{
    protected $baseUrl;

    // Declare properties
    protected $fibPaymentRepository;
    protected $fibAuthIntegrationService;

    public function __construct(FIBPaymentRepositoryService $fibPaymentRepository, FIBAuthIntegrationService $fibAuthIntegrationService)
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
        return $response;
    }

    /**
     * @throws Exception
     */
    protected function getRequest($url)
    {
        $token = $this->fibAuthIntegrationService->getToken();
        $response = retry(3, function () use ($url, $token) {
            return Http::withOptions([
                'verify' => false, // Disable SSL verification
            ])->withToken($token)
                ->get($url);
        }, 100);
        return $response;
    }

    /**
     * @throws Exception
     */
    public function createPayment($amount, $callback = null, $description = null, $redirectUri = null, $extraData = null)
    {
        try{
            $data = $this->getPaymentData($amount, $callback, $description, $redirectUri, $extraData);
            $paymentData = $this->postRequest("{$this->baseUrl}/payments", $data);
            if($paymentData->successful()) {
                $this->fibPaymentRepository->createPayment($paymentData->json(), $amount);
            }

            return $paymentData;
        }catch(Exception $e){
            Log::error('Failed to create payment', [
                'amount' => $amount,
                'callback' => $callback,
                'description' => $description,
                'redirect_uri' => $redirectUri,
                'extra_data' => $extraData,
                'exception_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception("An error occurred while creating the payment. Please try again later.");
        }
    }

    /**
     * @throws Exception
     */
    public function checkPaymentStatus($paymentId)
    {
        try{
            $response = $this->getRequest("{$this->baseUrl}/payments/{$paymentId}/status");
            if($response->successful()) {
                $this->fibPaymentRepository->updatePaymentStatus($paymentId, $response->json()['status']);
            }
            return $response;
        } catch (Exception $e) {
            Log::error('Failed to check payment status', [
                'payment_id' => $paymentId,
                'exception_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception(
                "An error occurred while checking the payment status. Please try again later.",
            );
        }
    }

    public function handleCallback($paymentId, $status)
    {
        $this->fibPaymentRepository->updatePaymentStatus($paymentId, $status);
    }

    public function getPaymentData($amount, $callback = null, $description = null, $redirectUri = null, $extraData = null)
    {
        return [
            'monetaryValue' => [
                'amount' => $amount,
                'currency' => config('fib.currency'),
            ],
            'statusCallbackUrl' => $callback ?? config('fib.callback'),
            'description' => $description ?? '',
            'redirectUri' => $redirectUri ?? '',
            'refundableFor' => config('fib.refundable_for'),
            ...$extraData ? ['extraData' => $extraData] : [],
        ];
    }

    /**
     * @throws Exception
     */
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

    public function cancelPayment($paymentId)
    {
        try {
            $response = $this->postRequest("{$this->baseUrl}/payments/{$paymentId}/cancel", data: []);
            if ($response->status() == 204) {
                $this->fibPaymentRepository->updatePaymentStatus($paymentId, FibPayment::CANCELED);
            }

            return $response;
        } catch (Exception $e) {
            Log::error('Failed to cancel payment', [
                'payment_id' => $paymentId,
                'exception_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception(
                "An error occurred while attempting to cancel the payment. Please try again later.",
            );
        }
    }
}
