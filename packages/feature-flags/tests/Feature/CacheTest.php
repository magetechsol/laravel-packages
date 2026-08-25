<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Tests\Feature;

use MageTech\FeatureFlags\Services\FeatureFlagService;
use MageTech\FeatureFlags\Tests\TestCase;

class CacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('mts-feature-flags.cache.enabled', true);
    }

    public function test_cache_enabled_flag(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'cached-flag',
            'name' => 'Cached Flag',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $this->assertTrue($service->enabled('cached-flag'));
        $this->assertTrue($service->enabled('cached-flag'));
    }

    public function test_clear_cache(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'clear-cache-flag',
            'name' => 'Clear Cache Flag',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $service->clearCache('clear-cache-flag');

        $this->assertTrue($service->enabled('clear-cache-flag'));
    }

    public function test_disable_clears_cache(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'disable-cache-flag',
            'name' => 'Disable Cache Flag',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $this->assertTrue($service->enabled('disable-cache-flag'));

        $service->disable('disable-cache-flag');

        $this->assertFalse($service->enabled('disable-cache-flag'));
    }
}
