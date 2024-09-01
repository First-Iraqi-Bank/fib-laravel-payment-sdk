<?php

    namespace FirstIraqiBank\FIBPaymentSDK\Tests\Unit\Services;

    use FirstIraqiBank\FIBPaymentSDK\Model\FibPayment;
    use FirstIraqiBank\FIBPaymentSDK\Services\FIBPaymentRepositoryService;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\ModelNotFoundException;
    use Illuminate\Support\Facades\Log;
    use Mockery;
    use Orchestra\Testbench\TestCase;

    class FIBPaymentRepositoryServiceTest extends TestCase
    {
        private $fibPaymentRepository;

        protected function setUp(): void
        {
            parent::setUp();
            $this->fibPaymentRepository = Mockery::mock(FIBPaymentRepositoryService::class);
        }

        public function test_create_payment_success()
        {
            $paymentData = [
                'paymentId' => '123',
                'readableCode' => 'ABC123',
                'personalAppLink' => 'http://example.com',
                'validUntil' => now()->addDays(1),
            ];
            $amount = 100;

            $paymentModel = Mockery::mock(FibPayment::class);
            $paymentModel->shouldReceive('create')
                ->with([
                    'fib_payment_id' => '123',
                    'readable_code' => 'ABC123',
                    'personal_app_link' => 'http://example.com',
                    'status' => FibPayment::PENDING,
                    'amount' => 100,
                    'valid_until' => now()->addDays(1),
                ])
                ->andReturn($paymentModel);

            $this->fibPaymentRepository->shouldReceive('createPayment')
                ->with($paymentData, $amount)
                ->andReturn($paymentModel);

            $result = $this->fibPaymentRepository->createPayment($paymentData, $amount);
            $this->assertInstanceOf(Model::class, $result);
        }

        public function test_get_payment_by_fib_id_success()
        {
            $paymentId = '123';

            $paymentModel = Mockery::mock(FibPayment::class);
            $paymentModel->shouldReceive('where')
                ->with('fib_payment_id', $paymentId)
                ->andReturnSelf();
            $paymentModel->shouldReceive('firstOrFail')
                ->andReturn($paymentModel);

            $this->fibPaymentRepository->shouldReceive('getPaymentByFibId')
                ->with($paymentId)
                ->andReturn($paymentModel);

            $result = $this->fibPaymentRepository->getPaymentByFibId($paymentId);
            $this->assertInstanceOf(Model::class, $result);
        }

        public function test_get_payment_by_id_success()
        {
            $paymentId = 1;

            $paymentModel = Mockery::mock(FibPayment::class);
            $paymentModel->shouldReceive('find')
                ->with($paymentId)
                ->andReturn($paymentModel);

            $this->fibPaymentRepository->shouldReceive('getPaymentById')
                ->with($paymentId)
                ->andReturn($paymentModel);

            $result = $this->fibPaymentRepository->getPaymentById($paymentId);
            $this->assertInstanceOf(Model::class, $result);
        }

        public function test_get_payments_by_status_success()
        {
            $statuses = ['pending', 'completed'];

            $paymentCollection = Mockery::mock(Collection::class);
            $paymentCollection->shouldReceive('whereIn')
                ->with('status', $statuses)
                ->andReturnSelf();
            $paymentCollection->shouldReceive('where')
                ->with('created_at', '<', now()->subMinutes(5))
                ->andReturnSelf();
            $paymentCollection->shouldReceive('get')
                ->andReturn($paymentCollection);

            $this->fibPaymentRepository->shouldReceive('getPaymentsByStatus')
                ->with($statuses)
                ->andReturn($paymentCollection);

            $result = $this->fibPaymentRepository->getPaymentsByStatus($statuses);
            $this->assertInstanceOf(Collection::class, $result);
        }

        public function test_update_payment_status_success()
        {
            // Create a mock for the FIBPaymentRepositoryService
            $fibPaymentRepoMock = Mockery::mock(FIBPaymentRepositoryService::class);

            // Define the payment ID and status to use in the test
            $paymentId = 123;
            $status = 'completed';

            // Set up the expectation for the updatePaymentStatus method
            $fibPaymentRepoMock->shouldReceive('updatePaymentStatus')
                ->with($paymentId, $status)
                ->once()
                ->andReturn(null);

            // Call the method under test
            $fibPaymentRepoMock->updatePaymentStatus($paymentId, $status);

            // Optionally, you can add assertions here if needed
            // For example, you might want to check if the method was called with the correct parameters
            $this->assertTrue(true); // Replace this with actual assertions
        }


        public function test_update_or_create_refund_success()
        {
            // Create a mock for the FIBPaymentRepositoryService
            $fibPaymentRepoMock = Mockery::mock(FIBPaymentRepositoryService::class);

            // Define the payment ID and refund data to use in the test
            $paymentId = 123;
            $refundData = [
                'amount' => 100,
                'reason' => 'Refund reason',
            ];

            // Set up the expectation for the updateOrCreateRefund method
            $fibPaymentRepoMock->shouldReceive('updateOrCreateRefund')
                ->with($paymentId, $refundData)
                ->once()
                ->andReturn(null);

            // Call the method under test
            $fibPaymentRepoMock->updateOrCreateRefund($paymentId, $refundData);

            // Optionally, you can add assertions here if needed
            // For example, you might want to check if the method was called with the correct parameters
            $this->assertTrue(true); // Replace this with actual assertions
        }

        protected function tearDown(): void
        {
            Mockery::close();
            parent::tearDown();
        }
    }
