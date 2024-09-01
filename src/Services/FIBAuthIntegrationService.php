<?php

    namespace FirstIraqiBank\FIBPaymentSDK\Services;

    use Exception;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;

    class FIBAuthIntegrationService
    {
        protected string $account;

        /**
         * FIBAuthIntegrationService constructor.
         * Set the account based on configuration.
         */
        public function __construct()
        {
            $this->account = config('fib.auth_account', 'default');
        }

        /**
         * Retrieve the access token from the FIB Payment API.
         *
         * @return string
         * @throws Exception
         */
        public function getToken(): string
        {
            try {
                $response = retry(3, function () {
                    return Http::withOptions([
                        'verify' => false, // Disable SSL verification
                    ])->asForm()
                        ->withBasicAuth(
                            config("fib.{$this->account}.client_id"),
                            config("fib.{$this->account}.secret")
                        )->post(config('fib.login'), [
                            'grant_type' => config('fib.grant'),
                        ]);
                }, 100);


               if ($response->successful() && isset($response->json()['access_token'])) {
                    return $response->json()['access_token'];
                }

                Log::error('Failed to retrieve access token from FIB Payment API.', [
                    'response' => $response->body(),
                ]);

                throw new Exception('Failed to retrieve access token.');
            } catch (Exception $e) {

                Log::error('Error occurred while retrieving access token from FIB Payment API.', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        }
    }
