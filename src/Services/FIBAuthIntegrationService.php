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
        $this->account = config('fib.default_auth_account');
    }

    /**
     * Set the account.
     */
    public function setAccount($account)
    {
        $this->account = $account;
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
            $response = retry(
                3,
                function () {
                    return Http::withOptions([
                        'verify' => false, // Disable SSL verification
                    ])
                        ->asForm()
                        ->withBasicAuth(config("fib.auth_accounts.{$this->account}.client_id"), config("fib.auth_accounts.{$this->account}.secret"))
                        ->post(config('fib.login'), [
                            'grant_type' => config('fib.grant'),
                        ]);
                },
                100,
            );

            if ($response->successful() && isset($response->json()['access_token'])) {
                return $response->json()['access_token'];
            }

            Log::error('FIB API call failed', [
                'status' => $response->status(),
                'response_body' => $response->body(),
            ]);
            throw new Exception('Failed to retrieve access token.');
        } catch (Exception $e) {
            Log::error('Failed to get token from FIB API', ['exception' => $e]);
            throw $e;
        }
    }
}
