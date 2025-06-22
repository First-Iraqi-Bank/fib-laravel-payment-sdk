<?php

namespace FirstIraqiBank\FIBPaymentSDK\Tests\Unit\Services;

use Exception;
use FirstIraqiBank\FIBPaymentSDK\Services\FIBAuthIntegrationService;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;

class FIBAuthIntegrationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up the configuration values
        config()->set('fib.default_auth_account', 'default');
        config()->set('fib.auth_accounts.default.client_id', 'test-client-id');
        config()->set('fib.auth_accounts.default.secret', 'test-secret');
        config()->set('fib.auth_accounts.second.client_id', 'test-client-id');
        config()->set('fib.auth_accounts.second.secret', 'test-secret');
        config()->set('fib.login', 'https://api.fib.com/login');
        config()->set('fib.grant', 'client_credentials');
    }

    public function test_it_retrieves_an_access_token_successfully()
    {
        // Arrange
        Http::fake([
            'https://api.fib.com/login' => Http::response(['access_token' => 'test-token'], 200),
        ]);

        // Act
        $service = new FIBAuthIntegrationService();
        $token = $service->getToken();

        // Assert
        $this->assertEquals('test-token', $token);
    }

    public function test_it_throws_an_exception_when_token_retrieval_fails()
    {
        // Arrange
        Http::fake([
            'https://api.fib.com/login' => Http::response('Error message', 400),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to retrieve access token.');

        // Act
        $service = new FIBAuthIntegrationService();
        $service->getToken();
    }

//        public function test_it_logs_and_throws_exception_on_error()
//        {
//            // Arrange
//            Http::fake(function ($request) {
//                throw new Exception('Network Error');
//            });
//
//
//            $this->expectException(Exception::class);
//            $this->expectExceptionMessage('Network Error');
//
//            // Act
//            $service = new FIBAuthIntegrationService();
//            $service->getToken();
//        }

    public function test_it_sets_the_account()
    {
        $service = new FIBAuthIntegrationService();

        // Use reflection to access the protected $account property
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('account');
        $property->setAccessible(true);

        $service->setAccount('custom_account');

        $this->assertEquals('custom_account', $property->getValue($service));
    }
}
