<?php

    namespace FirstIraqiBank\FIBPaymentSDK\Tests\Unit\Services;

    use FirstIraqiBank\FIBPaymentSDK\Model\FibPayment;
    use FirstIraqiBank\FIBPaymentSDK\Model\FibRefund;
    use FirstIraqiBank\FIBPaymentSDK\Services\FIBAuthIntegrationService;
    use FirstIraqiBank\FIBPaymentSDK\Services\FIBPaymentIntegrationService;
    use FirstIraqiBank\FIBPaymentSDK\Services\FIBPaymentRepositoryService;
    use Illuminate\Support\Facades\Http;
    use Mockery;
    use Orchestra\Testbench\TestCase;

    class FIBPaymentIntegrationServiceTest extends TestCase
    {
        protected function setUp(): void
        {
            parent::setUp();

            // Setup configuration
            config()->set('fib.base_url', 'https://api.fib.com');
            config()->set('fib.currency', 'USD');
            config()->set('fib.callback', 'https://your-callback-url.com');
            config()->set('fib.refundable_for', 30);
        }

        public function test_create_payment_success()
        {
            // Arrange
            $authServiceMock = Mockery::mock(FIBAuthIntegrationService::class);
            $authServiceMock->shouldReceive('getToken')->andReturn('test-token');

            $paymentRepositoryMock = Mockery::mock(FIBPaymentRepositoryService::class);
            $paymentRepositoryMock->shouldReceive('createPayment')->andReturn(Mockery::mock(FibPayment::class));

            $responseMock = Mockery::mock('Illuminate\Http\Client\Response');
            $responseMock->shouldReceive('successful')->andReturn(true);
            $responseMock->shouldReceive('json')->andReturn(['payment_id' => '12345']);

            Http::shouldReceive('withoutVerifying')->andReturnSelf();
            Http::shouldReceive('asJson')->andReturnSelf();
            Http::shouldReceive('withToken')->with('test-token')->andReturnSelf();
            Http::shouldReceive('withOptions')->andReturnSelf(); // Add this line
            Http::shouldReceive('post')->andReturn($responseMock);

            $service = new FIBPaymentIntegrationService($paymentRepositoryMock, $authServiceMock);

            // Act
            $result = $service->createPayment(1000);

            // Assert
            $this->assertInstanceOf(FibPayment::class, $result);
        }

        public function test_check_payment_status_success()
        {
            // Arrange
            $authServiceMock = Mockery::mock(FIBAuthIntegrationService::class);
            $authServiceMock->shouldReceive('getToken')->andReturn('test-token');

            $paymentRepositoryMock = Mockery::mock(FIBPaymentRepositoryService::class);
            $paymentRepositoryMock->shouldReceive('updatePaymentStatus')->with('12345', 'COMPLETED');

            $responseMock = Mockery::mock('Illuminate\Http\Client\Response');
            $responseMock->shouldReceive('successful')->andReturn(true);
            $responseMock->shouldReceive('json')->andReturn(['status' => 'COMPLETED']);

            Http::shouldReceive('withoutVerifying')->andReturnSelf();
            Http::shouldReceive('withToken')->with('test-token')->andReturnSelf();
            Http::shouldReceive('withOptions')->andReturnSelf(); // Add this line
            Http::shouldReceive('get')->andReturn($responseMock);

            $service = new FIBPaymentIntegrationService($paymentRepositoryMock, $authServiceMock);

            // Act
            $status = $service->checkPaymentStatus('12345');

            // Assert
            $this->assertEquals('COMPLETED', $status);
        }

        public function test_refund_success()
        {
            // Arrange
            $authServiceMock = Mockery::mock(FIBAuthIntegrationService::class);
            $authServiceMock->shouldReceive('getToken')->andReturn('test_token');

            $paymentRepositoryMock = Mockery::mock(FIBPaymentRepositoryService::class);
            $paymentRepositoryMock->shouldReceive('updateOrCreateRefund')
                ->once()
                ->with('payment_id_123', ['status' => FibRefund::SUCCESS]);

            $paymentRepositoryMock->shouldReceive('updatePaymentStatus')
                ->once()
                ->with('payment_id_123', FibPayment::REFUNDED);

            $responseMock = Mockery::mock('Illuminate\Http\Client\Response');
            $responseMock->shouldReceive('successful')->once()->andReturn(true);
            $responseMock->shouldReceive('status')->andReturn(202);

            Http::shouldReceive('withoutVerifying')->andReturnSelf();
            Http::shouldReceive('asJson')->andReturnSelf();
            Http::shouldReceive('withToken')->andReturnSelf();
            Http::shouldReceive('withOptions')->andReturnSelf(); // Add this line
            Http::shouldReceive('post')->once()->andReturn($responseMock);

            $service = new FIBPaymentIntegrationService($paymentRepositoryMock, $authServiceMock);

            // Act
            $service->refund('payment_id_123');

            $this->assertTrue(true);
        }

        protected function tearDown(): void
        {
            Mockery::close();
            parent::tearDown();
        }
    }
