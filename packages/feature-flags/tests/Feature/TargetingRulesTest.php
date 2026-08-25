<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Tests\Feature;

use MageTech\FeatureFlags\Enums\RuleOperator;
use MageTech\FeatureFlags\Enums\RuleType;
use MageTech\FeatureFlags\Models\FeatureFlag;
use MageTech\FeatureFlags\Services\FeatureFlagService;
use MageTech\FeatureFlags\Support\RuleEngine;
use MageTech\FeatureFlags\Tests\Fixtures\User;
use MageTech\FeatureFlags\Tests\TestCase;

class TargetingRulesTest extends TestCase
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

    public function test_user_id_rule(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'user-targeted',
            'name' => 'User Targeted',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $flag->rules()->create([
            'rule_type' => RuleType::UserId,
            'operator' => RuleOperator::Equals,
            'attribute' => 'id',
            'value' => '1',
            'priority' => 10,
        ]);

        $user1 = User::create(['name' => 'User 1', 'email' => 'u1@test.com']);
        $user2 = User::create(['name' => 'User 2', 'email' => 'u2@test.com']);

        $this->assertTrue($service->enabled('user-targeted', $user1));
        $this->assertFalse($service->enabled('user-targeted', $user2));
    }

    public function test_role_rule(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'admin-only',
            'name' => 'Admin Only',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $flag->rules()->create([
            'rule_type' => RuleType::Role,
            'operator' => RuleOperator::Equals,
            'attribute' => 'role',
            'value' => 'admin',
            'priority' => 10,
        ]);

        $admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'role' => 'admin']);
        $user = User::create(['name' => 'User', 'email' => 'user@test.com', 'role' => 'user']);

        $this->assertTrue($service->enabled('admin-only', $admin));
        $this->assertFalse($service->enabled('admin-only', $user));
    }

    public function test_email_rule(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'company-feature',
            'name' => 'Company Feature',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $flag->rules()->create([
            'rule_type' => RuleType::Email,
            'operator' => RuleOperator::Ends,
            'attribute' => 'email',
            'value' => '@company.com',
            'priority' => 10,
        ]);

        $companyUser = User::create(['name' => 'Company', 'email' => 'john@company.com']);
        $externalUser = User::create(['name' => 'External', 'email' => 'jane@gmail.com']);

        $this->assertTrue($service->enabled('company-feature', $companyUser));
        $this->assertFalse($service->enabled('company-feature', $externalUser));
    }

    public function test_team_rule(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'team-feature',
            'name' => 'Team Feature',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $flag->rules()->create([
            'rule_type' => RuleType::Team,
            'operator' => RuleOperator::Equals,
            'attribute' => 'team_id',
            'value' => '5',
            'priority' => 10,
        ]);

        $teamUser = User::create(['name' => 'Team', 'email' => 'team@test.com', 'team_id' => 5]);
        $otherUser = User::create(['name' => 'Other', 'email' => 'other@test.com', 'team_id' => 10]);

        $this->assertTrue($service->enabled('team-feature', $teamUser));
        $this->assertFalse($service->enabled('team-feature', $otherUser));
    }

    public function test_in_operator(): void
    {
        $service = app(FeatureFlagService::class);

        $flag = $service->create([
            'key' => 'multi-user',
            'name' => 'Multi User',
            'type' => 'boolean',
            'enabled' => true,
        ]);

        $flag->rules()->create([
            'rule_type' => RuleType::UserId,
            'operator' => RuleOperator::In,
            'attribute' => 'id',
            'value' => '1,2,3',
            'priority' => 10,
        ]);

        $user1 = User::create(['name' => 'U1', 'email' => 'u1@test.com']);
        $user5 = User::create(['name' => 'U5', 'email' => 'u5@test.com']);

        $this->assertTrue($service->enabled('multi-user', $user1));
        $this->assertFalse($service->enabled('multi-user', $user5));
    }
}
