<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Tests\Feature;

use MageTech\FeatureFlags\Services\FeatureFlagService;
use MageTech\FeatureFlags\Tests\Fixtures\User;
use MageTech\FeatureFlags\Tests\TestCase;

class FeatureFlagCRUDTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        User::createTable();
    }

    protected function tearDown(): void
    {
        User::dropTable();
        parent::tearDown();
    }

    public function test_can_create_feature_flag(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'new-dashboard',
            'name' => 'New Dashboard',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $this->assertNotNull($flag);
        $this->assertSame('new-dashboard', $flag->key);
        $this->assertTrue($flag->enabled);
    }

    public function test_can_find_flag_by_key(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'test-feature',
            'name' => 'Test Feature',
            'type' => 'boolean',
        ]);

        $flags = $service->getAll();
        $found = $flags->where('key', 'test-feature')->first();

        $this->assertNotNull($found);
        $this->assertSame('Test Feature', $found->name);
    }

    public function test_can_enable_flag(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'toggle-feature',
            'name' => 'Toggle Feature',
            'type' => 'boolean',
            'enabled' => false,
        ]);

        $flag = $service->enable('toggle-feature');

        $this->assertTrue($flag->enabled);
    }

    public function test_can_disable_flag(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'toggle-feature',
            'name' => 'Toggle Feature',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $flag = $service->disable('toggle-feature');

        $this->assertFalse($flag->enabled);
    }

    public function test_can_delete_flag(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'delete-me',
            'name' => 'Delete Me',
            'type' => 'boolean',
        ]);

        $result = $service->delete($flag);

        $this->assertTrue($result);
        $this->assertNull($service->getAll()->where('key', 'delete-me')->first());
    }

    public function test_can_update_flag(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'update-me',
            'name' => 'Old Name',
            'type' => 'boolean',
        ]);

        $updated = $service->update($flag, ['name' => 'New Name']);

        $this->assertSame('New Name', $updated->name);
    }
}
