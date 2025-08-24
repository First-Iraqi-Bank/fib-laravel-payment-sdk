<?php

namespace FirstIraqiBank\FIBPaymentSDK\database\Factories;

use Faker\Generator as Faker;
use FirstIraqiBank\FIBPaymentSDK\Model\FibPayment;

class FibRefundFactory
{
    public static function definition(Faker $faker): array
    {
        return [
            //                'payment_id' => FibPayment::factory(),
            'fib_trace_id' => $faker->uuid,
            'status' => 'PENDING',
            'refund_details' => $faker->sentence,
            'refund_failure_reason' => null,
        ];
    }
}
