<?php

    namespace FirstIraqiBank\FIBPaymentSDK\Tests\Unit\Services;

    use FirstIraqiBank\FIBPaymentSDK\Services\FIBAuthIntegrationService;
    use Mockery;
    use Illuminate\Http\Client\Factory as HttpClient;
    use Illuminate\Http\Client\Response;
    use Illuminate\Support\Facades\Log;
    use FirstIraqiBank\FIBPaymentSDK\Tests\TestCase;
    class FIBAuthIntegrationServiceTest extends TestCase
    {
        protected array $accountConfig;

        protected function setUp(): void
        {
            parent::setUp();

            // Define configuration values directly in the test method
            $this->accountConfig = [
                'login' => 'https://example.com/token',
                'default' => [
                    'client_id' => 'test-client-id',
                    'secret' => 'test-client-secret',
                ],
                'grant' => 'client_credentials',
            ];
        }

        /** @test */
        public function it_retrieves_a_token_successfully()
        {
            // Mock the HTTP client response
            $response = Mockery::mock(Response::class);
            $response->shouldReceive('body')->andReturn('{"access_token": "test-access-token"}');
            $response->shouldReceive('json')->andReturn(['access_token' => 'test-access-token']);
            $response->shouldReceive('successful')->andReturn(true);

            $httpClient = Mockery::mock(HttpClient::class);
            $httpClient->shouldReceive('withoutVerifying')->andReturnSelf();
            $httpClient->shouldReceive('asForm')->andReturnSelf();
            $httpClient->shouldReceive('withBasicAuth')
                ->with($this->accountConfig['default']['client_id'], $this->accountConfig['default']['secret'])
                ->andReturnSelf();
            $httpClient->shouldReceive('post')
                ->with($this->accountConfig['login'], ['grant_type' => $this->accountConfig['grant']])
                ->andReturn($response);

            $service = new FIBAuthIntegrationService($this->accountConfig, $httpClient);
            $token = $service->getToken();

            $this->assertEquals('test-access-token', $token);
        }

        /** @test */
        public function it_logs_error_when_token_retrieval_fails()
        {
            // Mock the HTTP client response
            $response = Mockery::mock(Response::class);
            $response->shouldReceive('body')->andReturn('{"error": "invalid_request"}');
            $response->shouldReceive('successful')->andReturn(false);

            $httpClient = Mockery::mock(HttpClient::class);
            $httpClient->shouldReceive('withoutVerifying')->andReturnSelf();
            $httpClient->shouldReceive('asForm')->andReturnSelf();
            $httpClient->shouldReceive('withBasicAuth')
                ->with($this->accountConfig['default']['client_id'], $this->accountConfig['default']['secret'])
                ->andReturnSelf();
            $httpClient->shouldReceive('post')
                ->with($this->accountConfig['login'], ['grant_type' => $this->accountConfig['grant']])
                ->andReturn($response);

            Log::shouldReceive('error')->once()->with('Failed to retrieve access token from FIB Payment API.', [
                'response' => '{"error": "invalid_request"}',
            ]);

            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Failed to retrieve access token.');

            $service = new FIBAuthIntegrationService($this->accountConfig, $httpClient);
            $service->getToken();
        }

        /** @test */
        public function it_logs_error_when_an_exception_is_thrown()
        {
            // Mock the HTTP client to throw an exception
            $httpClient = Mockery::mock(HttpClient::class);
            $httpClient->shouldReceive('withoutVerifying')->andReturnSelf();
            $httpClient->shouldReceive('asForm')->andReturnSelf();
            $httpClient->shouldReceive('withBasicAuth')
                ->with($this->accountConfig['default']['client_id'], $this->accountConfig['default']['secret'])
                ->andReturnSelf();
            $httpClient->shouldReceive('post')
                ->with($this->accountConfig['login'], ['grant_type' => $this->accountConfig['grant']])
                ->andThrow(new \Exception('HTTP request failed'));

            Log::shouldReceive('error')->once()->with(
                'Error occurred while retrieving access token from FIB Payment API.',
                Mockery::on(function ($context) {
                    return isset($context['message']) && $context['message'] === 'HTTP request failed' &&
                        isset($context['trace']) && is_string($context['trace']);
                })
            );

            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('HTTP request failed');

            $service = new FIBAuthIntegrationService($this->accountConfig, $httpClient);
            $service->getToken();
        }
    }
