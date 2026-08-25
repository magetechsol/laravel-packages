<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Tests;

use MageTech\AIGateway\AIGatewayServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            AIGatewayServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $app['config']->set('mts-ai.default', 'openai');
        $app['config']->set('mts-ai.default_model', 'gpt-4o');
        $app['config']->set('mts-ai.audit.enabled', false);
        $app['config']->set('mts-ai.rate_limits.enabled', false);
        $app['config']->set('mts-ai.quotas.enabled', false);
        $app['config']->set('mts-ai.cache.enabled', false);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function setUpDatabase(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
