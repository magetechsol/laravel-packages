<?php

declare(strict_types=1);

namespace Tests;

use MageTech\ApiToolkit\ApiToolkitServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders(): array
    {
        return [
            ApiToolkitServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('mts-api.response.envelope', true);
        $app['config']->set('mts-api.response.include_request_id', true);
        $app['config']->set('mts-api.response.include_timestamp', true);
        $app['config']->set('mts-api.response.include_api_version', true);
        $app['config']->set('mts-api.versioning.enabled', true);
        $app['config']->set('mts-api.versioning.default', 'v1');
        $app['config']->set('mts-api.exception_handling.enabled', true);
        $app['config']->set('mts-api.exception_handling.hide_stack_traces', true);
    }

    protected function setUp(): void
    {
        parent::setUp();
    }
}
