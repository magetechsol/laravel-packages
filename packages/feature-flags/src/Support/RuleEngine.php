<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Support;

use Illuminate\Support\Manager;
use MageTech\FeatureFlags\Contracts\FeatureRuleContract;
use MageTech\FeatureFlags\Enums\RuleOperator;
use MageTech\FeatureFlags\Enums\RuleType;
use MageTech\FeatureFlags\Models\FeatureFlagRule;
use MageTech\FeatureFlags\Rules\AttributeRule;
use MageTech\FeatureFlags\Rules\EmailRule;
use MageTech\FeatureFlags\Rules\EnvironmentRule;
use MageTech\FeatureFlags\Rules\OrganizationRule;
use MageTech\FeatureFlags\Rules\PermissionRule;
use MageTech\FeatureFlags\Rules\RoleRule;
use MageTech\FeatureFlags\Rules\TeamRule;
use MageTech\FeatureFlags\Rules\UserIdRule;

class RuleEngine extends Manager
{
    public function evaluate(FeatureFlagRule $rule, mixed $subject): bool
    {
        $driver = $this->driver($rule->rule_type->value);

        return $driver->evaluate($rule, $subject, $rule->operator);
    }

    public function getDefaultDriver(): string
    {
        return RuleType::Attribute->value;
    }

    public function createUserIdDriver(): FeatureRuleContract
    {
        return new UserIdRule();
    }

    public function createEmailDriver(): FeatureRuleContract
    {
        return new EmailRule();
    }

    public function createRoleDriver(): FeatureRuleContract
    {
        return new RoleRule();
    }

    public function createPermissionDriver(): FeatureRuleContract
    {
        return new PermissionRule();
    }

    public function createTeamDriver(): FeatureRuleContract
    {
        return new TeamRule();
    }

    public function createOrganizationDriver(): FeatureRuleContract
    {
        return new OrganizationRule();
    }

    public function createEnvironmentDriver(): FeatureRuleContract
    {
        return new EnvironmentRule();
    }

    public function createAttributeDriver(): FeatureRuleContract
    {
        return new AttributeRule();
    }

    public function registerRule(string $type, FeatureRuleContract $rule): void
    {
        $this->extend($type, fn () => $rule);
    }

    public function supports(string $type): bool
    {
        return in_array($type, $this->getDrivers());
    }
}
