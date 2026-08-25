<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Tests\Feature;

use MageTech\FeatureFlags\Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('mts-feature-flags.api.enabled', true);
        $this->app['config']->set('mts-feature-flags.api.middleware', ['api']);
    }

    public function test_can_list_flags(): void
    {
        \MageTech\FeatureFlags\Models\FeatureFlag::create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'key' => 'api-list',
            'name' => 'API List',
            'type' => 'boolean',
        ]);

        $response = $this->getJson('/api/feature-flags');

        $response->assertOk();
        $response->assertJsonFragment(['key' => 'api-list']);
    }

    public function test_can_show_flag(): void
    {
        \MageTech\FeatureFlags\Models\FeatureFlag::create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'key' => 'api-show',
            'name' => 'API Show',
            'type' => 'boolean',
        ]);

        $response = $this->getJson('/api/feature-flags/api-show');

        $response->assertOk();
        $response->assertJsonFragment(['key' => 'api-show']);
    }

    public function test_can_create_flag(): void
    {
        $response = $this->postJson('/api/feature-flags', [
            'key' => 'api-create',
            'name' => 'API Create',
            'type' => 'boolean',
        ]);

        $response->assertCreated();
        $response->assertJsonFragment(['key' => 'api-create']);
    }

    public function test_can_enable_flag(): void
    {
        \MageTech\FeatureFlags\Models\FeatureFlag::create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'key' => 'api-enable',
            'name' => 'API Enable',
            'type' => 'boolean',
            'enabled' => false,
        ]);

        $response = $this->postJson('/api/feature-flags/api-enable/enable');

        $response->assertOk();
    }

    public function test_can_disable_flag(): void
    {
        \MageTech\FeatureFlags\Models\FeatureFlag::create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'key' => 'api-disable',
            'name' => 'API Disable',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $response = $this->postJson('/api/feature-flags/api-disable/disable');

        $response->assertOk();
    }

    public function test_can_delete_flag(): void
    {
        \MageTech\FeatureFlags\Models\FeatureFlag::create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'key' => 'api-delete',
            'name' => 'API Delete',
            'type' => 'boolean',
        ]);

        $response = $this->deleteJson('/api/feature-flags/api-delete');

        $response->assertOk();
    }

    public function test_evaluate_flag(): void
    {
        \MageTech\FeatureFlags\Models\FeatureFlag::create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'key' => 'api-evaluate',
            'name' => 'API Evaluate',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $response = $this->postJson('/api/feature-flags/api-evaluate/evaluate');

        $response->assertOk();
        $response->assertJsonFragment(['enabled' => true]);
    }
}
