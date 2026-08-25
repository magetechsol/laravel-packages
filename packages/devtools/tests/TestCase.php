<?php

declare(strict_types=1);

namespace MageTech\DevTools\Tests;

use MageTech\DevTools\DevToolsServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            DevToolsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $app['config']->set('mts-devtools.enabled', true);
        $app['config']->set('mts-devtools.dashboard', true);
        $app['config']->set('mts-devtools.commands', true);
        $app['config']->set('mts-devtools.password', null);
        $app['config']->set('mts-devtools.allowed_ips', ['127.0.0.1', '::1']);
        $app['config']->set('mts-devtools.collectors.application', true);
        $app['config']->set('mts-devtools.collectors.performance', true);
        $app['config']->set('mts-devtools.collectors.security', true);
        $app['config']->set('mts-devtools.collectors.packages', true);
    }
}
