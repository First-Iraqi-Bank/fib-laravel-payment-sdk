<?php

    namespace FirstIraqiBank\FIBPaymentSDK\database\Factories;

    use Faker\Generator as Faker;
    use FirstIraqiBank\FIBPaymentSDK\Model\FibPayment;

    class FibPaymentFactory
    {
        public static function definition(Faker $faker): array
        {
            return [
                'fib_payment_id' => $faker->uuid,
                'readable_code' => $faker->slug,
                'personal_app_link' => $faker->url,
                'payment_status' => $faker->randomElement([FibPayment::PENDING, FibPayment::PAID, FibPayment::UNPAID]),
                'amount' => $faker->numberBetween(10, 1000),
                'valid_until' => $faker->dateTimeBetween('-1 week', '+1 month'),
                'created_at' => $faker->dateTimeBetween('-8 day', 'now'),
            ];
        }
    }
