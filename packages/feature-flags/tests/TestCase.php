<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MageTech\FeatureFlags\FeatureFlagsServiceProvider;
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
            FeatureFlagsServiceProvider::class,
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
        $app['config']->set('mts-feature-flags.cache.enabled', false);
        $app['config']->set('mts-feature-flags.events.dispatch_evaluated', false);
        $app['config']->set('mts-feature-flags.helpers.enabled', true);
        $app['config']->set('mts-feature-flags.blade.enabled', true);
    }

    protected function createFeatureFlag(array $overrides = []): \MageTech\FeatureFlags\Models\FeatureFlag
    {
        $data = array_merge([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'key' => 'test-flag-' . \Illuminate\Support\Str::random(8),
            'name' => 'Test Feature Flag',
            'description' => 'A test feature flag',
            'type' => 'boolean',
            'enabled' => true,
        ], $overrides);

        return \MageTech\FeatureFlags\Models\FeatureFlag::create($data);
    }
}
