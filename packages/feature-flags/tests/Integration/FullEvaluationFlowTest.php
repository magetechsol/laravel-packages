<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Tests\Integration;

use Illuminate\Support\Facades\Event;
use MageTech\FeatureFlags\Events\FeatureCreated;
use MageTech\FeatureFlags\Events\FeatureDisabled;
use MageTech\FeatureFlags\Events\FeatureEnabled;
use MageTech\FeatureFlags\Services\FeatureFlagService;
use MageTech\FeatureFlags\Tests\Fixtures\User;
use MageTech\FeatureFlags\Tests\TestCase;

class FullEvaluationFlowTest extends TestCase
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

    public function test_full_lifecycle(): void
    {
        Event::fake([FeatureCreated::class, FeatureEnabled::class, FeatureDisabled::class]);

        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'lifecycle-flag',
            'name' => 'Lifecycle Flag',
            'type' => 'boolean',
            'enabled' => false,
        ]);

        Event::assertDispatched(FeatureCreated::class);

        $this->assertFalse($service->enabled('lifecycle-flag'));

        $service->enable('lifecycle-flag');

        Event::assertDispatched(FeatureEnabled::class);

        $this->assertTrue($service->enabled('lifecycle-flag'));

        $service->disable('lifecycle-flag');

        Event::assertDispatched(FeatureDisabled::class);

        $this->assertFalse($service->enabled('lifecycle-flag'));
    }

    public function test_targeting_with_override_precedence(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'precedence-flag',
            'name' => 'Precedence Flag',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $flag->rules()->create([
            'rule_type' => \MageTech\FeatureFlags\Enums\RuleType::Role,
            'operator' => \MageTech\FeatureFlags\Enums\RuleOperator::Equals,
            'attribute' => 'role',
            'value' => 'admin',
            'priority' => 10,
        ]);

        $admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'role' => 'admin']);
        $user = User::create(['name' => 'User', 'email' => 'user@test.com', 'role' => 'user']);

        $this->assertTrue($service->enabled('precedence-flag', $admin));
        $this->assertFalse($service->enabled('precedence-flag', $user));

        $flag->overrides()->create([
            'subject_type' => get_class($user),
            'subject_id' => $user->id,
            'enabled' => true,
        ]);

        $this->assertTrue($service->enabled('precedence-flag', $user));
    }

    public function test_facade_usage(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'facade-flag',
            'name' => 'Facade Flag',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $this->assertTrue(\MageTech\FeatureFlags\Facades\Feature::enabled('facade-flag'));
        $this->assertFalse(\MageTech\FeatureFlags\Facades\Feature::disabled('facade-flag'));
    }

    public function test_helper_functions(): void
    {
        $service = app(FeatureFlagService::class);

        $service->create([
            'key' => 'helper-flag',
            'name' => 'Helper Flag',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $this->assertTrue(feature_enabled('helper-flag'));
        $this->assertFalse(feature_disabled('helper-flag'));
    }
}
