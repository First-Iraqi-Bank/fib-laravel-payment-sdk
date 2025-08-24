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
    public function setAccount($account): void
    {
        $this->account = $account;
    }

    /**
     * Retrieve the access token from the FIB Payment API.
     *
     * @throws Exception
     */
    public function getToken(): ?string
    {
        try {
            $response = Http::asForm()
                ->withoutVerifying()
                ->withBasicAuth(config("fib.auth_accounts.{$this->account}.client_id"), config("fib.auth_accounts.{$this->account}.secret"))
                ->retry(times: 3, sleepMilliseconds: 100, throw: false)
                ->post(config('fib.login'), [
                    'grant_type' => config('fib.grant'),
                ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('Fib Payment SDK: Getting token failed', [
                'status' => $response->status(),
                'response_body' => $response->body(),
            ]);

        } catch (Exception $e) {

            Log::error('Fib Payment SDK: Exception while getting token', ['exception' => $e]);
        }

        return null;
    }
}
