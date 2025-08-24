<?php

namespace FirstIraqiBank\FIBPaymentSDK\Tests\Factories;

use Faker\Factory as Faker;
use FirstIraqiBank\FIBPaymentSDK\database\factories\FibPaymentFactory;
use FirstIraqiBank\FIBPaymentSDK\database\factories\FibRefundFactory;
use FirstIraqiBank\FIBPaymentSDK\Model\FibPayment;
use FirstIraqiBank\FIBPaymentSDK\Model\FibRefund;

class FibPaymentFactoryHelper
{
    public static function createPayment(array $attributes = [])
    {
        $faker = Faker::create();
        $data = FibPaymentFactory::definition($faker);

        return FibPayment::create(array_merge($data, $attributes));
    }

    public static function createRefund(array $attributes = [])
    {
        $faker = Faker::create();

        // Create a FibPayment and get its ID
        $payment = self::createPayment();

        // Merge the payment_id into the refund attributes
        $attributes['payment_id'] = $payment->id;

        // Generate FibRefund data and create the FibRefund instance
        $data = FibRefundFactory::definition($faker);

        return FibRefund::create(array_merge($data, $attributes));
    }
}
