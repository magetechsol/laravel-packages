<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MageTech\Webhooks\WebhooksServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [
            WebhooksServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('mts-webhooks.security.verify_hmac', false);
        $app['config']->set('mts-webhooks.security.verify_timestamp', false);
        $app['config']->set('mts-webhooks.security.ip_restrictions', []);
        $app['config']->set('mts-webhooks.processing.handler_map', []);
        $app['config']->set('mts-webhooks.logging.enabled', false);
    }

    protected function setUpDatabase(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
