<?php

declare(strict_types=1);

namespace MageTech\Workflow\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MageTech\Workflow\WorkflowServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            WorkflowServiceProvider::class,
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
        $app['config']->set('mts-workflow.audit.enabled', true);
        $app['config']->set('mts-workflow.concurrency.enabled', false);
    }
}
