<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Tests\Unit\Enums;

use MageTech\FeatureFlags\Enums\FeatureFlagStatus;
use MageTech\FeatureFlags\Enums\FeatureFlagType;
use MageTech\FeatureFlags\Enums\RuleOperator;
use MageTech\FeatureFlags\Enums\RuleType;
use PHPUnit\Framework\TestCase;

class FeatureFlagTypeTest extends TestCase
{
    public function test_boolean_type(): void
    {
        $type = FeatureFlagType::Boolean;

        $this->assertSame('boolean', $type->value);
        $this->assertSame('Boolean', $type->label());
        $this->assertFalse($type->requiresSubject());
    }

    public function test_percentage_type(): void
    {
        $type = FeatureFlagType::Percentage;

        $this->assertSame('percentage', $type->value);
        $this->assertTrue($type->requiresSubject());
    }

    public function test_variant_type(): void
    {
        $type = FeatureFlagType::Variant;

        $this->assertSame('variant', $type->value);
        $this->assertTrue($type->requiresSubject());
    }

    public function test_config_type(): void
    {
        $type = FeatureFlagType::Config;

        $this->assertSame('config', $type->value);
        $this->assertTrue($type->requiresSubject());
    }
}

class RuleTypeTest extends TestCase
{
    public function test_rule_types(): void
    {
        $this->assertSame('user_id', RuleType::UserId->value);
        $this->assertSame('email', RuleType::Email->value);
        $this->assertSame('role', RuleType::Role->value);
        $this->assertSame('permission', RuleType::Permission->value);
        $this->assertSame('environment', RuleType::Environment->value);
        $this->assertSame('attribute', RuleType::Attribute->value);
    }

    public function test_environment_does_not_require_subject(): void
    {
        $this->assertFalse(RuleType::Environment->requiresSubject());
    }

    public function test_user_id_requires_subject(): void
    {
        $this->assertTrue(RuleType::UserId->requiresSubject());
    }
}

class RuleOperatorTest extends TestCase
{
    public function test_operators(): void
    {
        $this->assertSame('equals', RuleOperator::Equals->value);
        $this->assertSame('not_equals', RuleOperator::NotEquals->value);
        $this->assertSame('contains', RuleOperator::Contains->value);
        $this->assertSame('in', RuleOperator::In->value);
        $this->assertSame('regex', RuleOperator::Regex->value);
    }
}

class FeatureFlagStatusTest extends TestCase
{
    public function test_status(): void
    {
        $this->assertTrue(FeatureFlagStatus::Active->isActive());
        $this->assertFalse(FeatureFlagStatus::Inactive->isActive());
        $this->assertFalse(FeatureFlagStatus::Archived->isActive());
    }
}
