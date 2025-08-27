<?php

namespace FirstIraqiBank\FIBPaymentSDK\Services\Contracts;

interface FIBPaymentIntegrationServiceInterface
{
    public function createPayment(int $amount, ?string $callback, ?string $description, ?string $redirectUri);

    public function checkPaymentStatus($paymentId);

    public function handleCallback(string $paymentId, string $status);

    public function refund(string $paymentId);

    public function cancelPayment(string $paymentId);
}
