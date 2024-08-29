<?php

    namespace FirstIraqiBank\FIBPaymentSDK\Tests\Feature\services;

    use FirstIraqiBank\FIBPaymentSDK\Tests\Factories\FibPaymentFactoryHelper;
    use FirstIraqiBank\FIBPaymentSDK\Tests\TestCase;
    use Illuminate\Foundation\Testing\DatabaseMigrations;

    class FibDbTest extends TestCase
    {
        use DatabaseMigrations;

        public function testCreateFibPayment()
        {
            $fibPayment = FibPaymentFactoryHelper::createPayment();

            $this->assertDatabaseHas('fib_payments', [
                'fib_payment_id' => $fibPayment->fib_payment_id,
            ]);
        }


        public function testCreateFibRefund()
        {
            $fibRefund = FibPaymentFactoryHelper::createRefund();
            $this->assertDatabaseHas('fib_refunds', [
                'payment_id' => $fibRefund->payment_id,
            ]);
        }


    }
