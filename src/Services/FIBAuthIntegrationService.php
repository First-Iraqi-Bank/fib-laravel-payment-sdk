<?php

namespace FirstIraqiBank\FIBPaymentSDK\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
                ->withBasicAuth((string) config("fib.auth_accounts.{$this->account}.client_id"), (string) config("fib.auth_accounts.{$this->account}.secret"))
                ->post(config('fib.login'), [
                    'grant_type' => config('fib.grant'),
                ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

        } catch (Exception $e) {

            Log::error('Fib Payment SDK: Exception while getting token', ['exception' => $e]);

            throw $e;
        }

        Log::error('Fib Payment SDK: Getting token failed', [
            'status' => $response->status(),
            'response_body' => $response->body(),
        ]);

        throw new HttpException(424, 'Fib Payment SDK: Getting token failed');
    }
}
