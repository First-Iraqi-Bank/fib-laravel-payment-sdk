<?php

namespace FirstIraqiBank\FIBPaymentSDK\Services;

use Exception;
use FirstIraqiBank\FIBPaymentSDK\Model\FibPayment;
use FirstIraqiBank\FIBPaymentSDK\Model\FibRefund;
use FirstIraqiBank\FIBPaymentSDK\Services\Contracts\FIBPaymentIntegrationServiceInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FIBPaymentIntegrationService implements FIBPaymentIntegrationServiceInterface
{
    protected $baseUrl;

    protected $fibPaymentRepository;

    protected $fibAuthIntegrationService;

    public function __construct(FIBPaymentRepositoryService $fibPaymentRepository, FIBAuthIntegrationService $fibAuthIntegrationService)
    {
        $this->fibPaymentRepository = $fibPaymentRepository;
        $this->fibAuthIntegrationService = $fibAuthIntegrationService;
        $this->baseUrl = config('fib.base_url');
    }

    private function postRequest(string $url, array $data): Response
    {
        $token = $this->fibAuthIntegrationService->getToken();

        return retry(3, function () use ($url, $data, $token) {
            return Http::asJson()
                ->withoutVerifying()
                ->withToken($token)
                ->post($url, $data);
        }, 100);
    }

    protected function getRequest($url): Response
    {
        $token = $this->fibAuthIntegrationService->getToken();

        return retry(3, function () use ($url, $token) {
            return Http::withoutVerifying()
                ->withToken($token)
                ->get($url);
        }, 100);
    }

    public function createPayment($amount, $callback = null, $description = null, $redirectUri = null, $extraData = null): PromiseInterface|Response|null
    {
        try {
            $data = $this->getPaymentData($amount, $callback, $description, $redirectUri, $extraData);

            $paymentData = $this->postRequest("{$this->baseUrl}/payments", $data);

            if ($paymentData->successful()) {
                $this->fibPaymentRepository->createPayment($paymentData->json(), $amount);
            }

            return $paymentData;

        } catch (Exception $e) {
            Log::error('Fib Payment SDK: Failed while creating payment', [
                'amount' => $amount,
                'callback' => $callback,
                'description' => $description,
                'redirect_uri' => $redirectUri,
                'extra_data' => $extraData,
                'exception_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return null;
    }

    public function checkPaymentStatus($paymentId): Response|PromiseInterface|null
    {
        try {

            $response = $this->getRequest("{$this->baseUrl}/payments/{$paymentId}/status");

            if ($response->successful()) {

                $this->fibPaymentRepository->updatePaymentStatus($paymentId, $response->json()['status']);
            }

            return $response;

        } catch (Exception $e) {
            Log::error('Fib Payment SDK: Failed while getting payment status', [
                'payment_id' => $paymentId,
                'exception_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return null;
    }

    public function handleCallback($paymentId, $status): void
    {
        $this->fibPaymentRepository->updatePaymentStatus($paymentId, $status);
    }

    public function getPaymentData($amount, $callback = null, $description = null, $redirectUri = null, $extraData = null): array
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

    public function refund($paymentId): void
    {
        try {
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
        } catch (Exception $e) {
            Log::error('Fib Payment SDK: Failed while issuing refund', [
                'payment_id' => $paymentId,
                'exception_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function cancelPayment($paymentId): PromiseInterface|Response|null
    {
        try {
            $response = $this->postRequest("{$this->baseUrl}/payments/{$paymentId}/cancel", data: []);
            if ($response->status() == 204) {
                $this->fibPaymentRepository->updatePaymentStatus($paymentId, FibPayment::CANCELED);
            }

            return $response;
        } catch (Exception $e) {
            Log::error('Fib Payment SDK: Failed while cancelling payment', [
                'payment_id' => $paymentId,
                'exception_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return null;
    }
}
