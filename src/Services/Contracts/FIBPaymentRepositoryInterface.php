<?php

namespace FirstIraqiBank\FIBPaymentSDK\Services\Contracts;

interface FIBPaymentRepositoryInterface
{
    public function createPayment(array $paymentData, int $amount);

    public function getPaymentByFibId(string $paymentId);

    public function getPaymentsByStatus(array $statuses);

    public function updatePaymentStatus(string $paymentId, string $status);

    public function getPurchase(int $paymentId);

    public function updateOrCreateRefund(string $paymentId, array $refundData);
}
