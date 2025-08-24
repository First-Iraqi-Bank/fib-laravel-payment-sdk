<?php

namespace FirstIraqiBank\FIBPaymentSDK\Tests;

use FirstIraqiBank\FIBPaymentSDK\FIBPaymentServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withFactories(__DIR__.'/../database/factories');
    }

    protected function getPackageProviders($app): array
    {
        return [
            FIBPaymentServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'test_db');
        $app['config']->set('database.connections.test_db', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }
}
