<?php

declare(strict_types=1);

namespace MageTech\Audit\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;
use MageTech\Audit\AuditServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
    }

    protected function getPackageProviders($app): array
    {
        return [
            AuditServiceProvider::class,
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

        $app['config']->set('audit.driver', 'database');
        $app['config']->set('audit.connection', null);
        $app['config']->set('audit.integrity.enabled', false);
        $app['config']->set('audit.tenancy.enabled', false);
        $app['config']->set('audit.queue', false);
    }
}
