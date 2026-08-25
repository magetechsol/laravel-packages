<?php

declare(strict_types=1);

namespace MageTech\SaaS\Tests;

use MageTech\SaaS\SaaSTenancyServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            SaaSTenancyServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $app['config']->set('mts-saas.strategy', 'shared');
        $app['config']->set('mts-saas.key_type', 'int');
        $app['config']->set('mts-saas.central_domains', ['localhost']);
        $app['config']->set('mts-saas.cache.enabled', false);
        $app['config']->set('mts-saas.queue.enabled', false);
        $app['config']->set('mts-saas.storage.enabled', false);

        $app['config']->set('mts-saas.resolvers.subdomain.enabled', false);
        $app['config']->set('mts-saas.resolvers.path.enabled', false);
        $app['config']->set('mts-saas.resolvers.header.enabled', false);
        $app['config']->set('mts-saas.resolvers.session.enabled', false);
        $app['config']->set('mts-saas.resolvers.cookie.enabled', false);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function setUpDatabase(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        \Illuminate\Support\Facades\Schema::create('users', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });
    }
}
