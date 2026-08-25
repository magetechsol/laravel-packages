<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Tests\Feature;

use MageTech\FeatureFlags\Services\FeatureFlagService;
use MageTech\FeatureFlags\Tests\Fixtures\User;
use MageTech\FeatureFlags\Tests\TestCase;

class FeatureFlagEvaluationTest extends TestCase
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

    public function test_boolean_flag_enabled(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'new-dashboard',
            'name' => 'New Dashboard',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $this->assertTrue($service->enabled('new-dashboard'));
    }

    public function test_boolean_flag_disabled(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'old-dashboard',
            'name' => 'Old Dashboard',
            'type' => 'boolean',
            'enabled' => false,
        ]);

        $this->assertFalse($service->enabled('old-dashboard'));
    }

    public function test_disabled_method(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'legacy-feature',
            'name' => 'Legacy Feature',
            'type' => 'boolean',
            'enabled' => false,
        ]);

        $this->assertTrue($service->disabled('legacy-feature'));
    }

    public function test_nonexistent_flag_returns_false(): void
    {
        $service = app(FeatureFlagService::class);

        $this->assertFalse($service->enabled('nonexistent-flag'));
    }

    public function test_active_method(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'active-flag',
            'name' => 'Active Flag',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $this->assertTrue($service->active('active-flag'));
    }

    public function test_scheduled_flag_not_started(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'future-flag',
            'name' => 'Future Flag',
            'type' => 'boolean',
            'enabled' => true,
            'starts_at' => now()->addDays(30),
        ]);

        $this->assertFalse($service->enabled('future-flag'));
    }

    public function test_scheduled_flag_ended(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'expired-flag',
            'name' => 'Expired Flag',
            'type' => 'boolean',
            'enabled' => true,
            'ends_at' => now()->subDay(),
        ]);

        $this->assertFalse($service->enabled('expired-flag'));
    }

    public function test_percentage_rollout(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'rollout-feature',
            'name' => 'Rollout Feature',
            'type' => 'percentage',
            'enabled' => true,
            'rollout_percentage' => 50,
        ]);

        $results = [];
        for ($i = 0; $i < 100; $i++) {
            $user = User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
            ]);
            $results[] = $service->enabled('rollout-feature', $user);
        }

        $trueCount = count(array_filter($results));
        $this->assertGreaterThan(30, $trueCount);
        $this->assertLessThan(70, $trueCount);
    }

    public function test_variant_flag(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'checkout-ui',
            'name' => 'Checkout UI',
            'type' => 'variant',
            'enabled' => true,
        ]);

        $flag->variants()->create([
            'key' => 'v1',
            'name' => 'Version 1',
            'weight' => 1,
            'enabled' => true,
        ]);

        $flag->variants()->create([
            'key' => 'v2',
            'name' => 'Version 2',
            'weight' => 1,
            'enabled' => true,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $variant = $service->variant('checkout-ui', $user);
        $this->assertContains($variant, ['v1', 'v2']);
    }

    public function test_for_method(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'user-feature',
            'name' => 'User Feature',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertTrue($service->for($user)->enabled('user-feature'));
    }
}
