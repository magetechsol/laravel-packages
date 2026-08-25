<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Tests\Feature;

use MageTech\FeatureFlags\Models\FeatureFlagOverride;
use MageTech\FeatureFlags\Services\FeatureFlagService;
use MageTech\FeatureFlags\Tests\Fixtures\User;
use MageTech\FeatureFlags\Tests\TestCase;

class OverrideTest extends TestCase
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

    public function test_override_enables_for_user(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'overridden-flag',
            'name' => 'Overridden Flag',
            'type' => 'boolean',
            'enabled' => false,
        ]);

        $user = User::create(['name' => 'Test', 'email' => 'test@test.com']);

        $flag->overrides()->create([
            'subject_type' => get_class($user),
            'subject_id' => $user->id,
            'enabled' => true,
        ]);

        $this->assertTrue($service->enabled('overridden-flag', $user));
    }

    public function test_override_disables_for_user(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'override-disable',
            'name' => 'Override Disable',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $user = User::create(['name' => 'Test', 'email' => 'test@test.com']);

        $flag->overrides()->create([
            'subject_type' => get_class($user),
            'subject_id' => $user->id,
            'enabled' => false,
        ]);

        $this->assertFalse($service->enabled('override-disable', $user));
    }

    public function test_expired_override_ignored(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'expired-override',
            'name' => 'Expired Override',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $user = User::create(['name' => 'Test', 'email' => 'test@test.com']);

        $flag->overrides()->create([
            'subject_type' => get_class($user),
            'subject_id' => $user->id,
            'enabled' => false,
            'expires_at' => now()->subDay(),
        ]);

        $this->assertTrue($service->enabled('expired-override', $user));
    }
}
