<?php

namespace FirstIraqiBank\FIBPaymentSDK\Services;

use Exception;
use FirstIraqiBank\FIBPaymentSDK\Model\FibPayment;
use FirstIraqiBank\FIBPaymentSDK\Services\Contracts\FIBPaymentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class FIBPaymentRepositoryService implements FIBPaymentRepositoryInterface
{
    /**
     * Create a new payment record.
     */
    public function createPayment(array $paymentData, int $amount)
    {
        return FibPayment::query()->create([
            'fib_payment_id' => $paymentData['paymentId'],
            'readable_code' => $paymentData['readableCode'],
            'personal_app_link' => $paymentData['personalAppLink'],
            'status' => FibPayment::PENDING,
            'amount' => $amount,
            'valid_until' => $paymentData['validUntil'],
        ]);
    }

    /**
     * Retrieve a payment by its FIB payment ID.
     *
     * @throws ModelNotFoundException
     */
    public function getPaymentByFibId(string $paymentId): Model
    {
        return FibPayment::query()->where('fib_payment_id', $paymentId)->firstOrFail();
    }

    /**
     * Retrieve a payment by its ID.
     */
    public function getPaymentById(int $paymentId): ?Model
    {
        return FibPayment::query()->find($paymentId);
    }

    /**
     * Retrieve payments by their status.
     */
    public function getPaymentsByStatus(array $statuses): Collection
    {
        return FibPayment::query()->whereIn('status', $statuses)->where('created_at', '<', now()->subMinutes(5))->get();
    }

    /**
     * Update the status of a payment.
     *
     * @throws Exception
     */
    public function updatePaymentStatus(string $paymentId, string $status): void
    {
        try {
            $this->getPaymentByFibId($paymentId)->update(['status' => $status]);
        } catch (ModelNotFoundException $e) {
            Log::error('Payment not found', ['paymentId' => $paymentId, 'exception' => $e]);
            throw new Exception("Payment with ID {$paymentId} not found.");
        }
    }

    /**
     * Retrieve the purchase associated with a payment.
     */
    public function getPurchase(int $paymentId): ?Model
    {
        return $this->getPaymentById($paymentId)->purchase()->first();
    }

    /**
     * Update or create a refund record.
     */
    public function updateOrCreateRefund(string $paymentId, array $refundData): void
    {
        $fibPayment = $this->getPaymentByFibId($paymentId);
        $fibPayment->refund()->updateOrCreate(['payment_id' => $fibPayment->id], $refundData);
    }
}
