<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Enums;

enum FeatureFlagType: string
{
    case Boolean = 'boolean';
    case Percentage = 'percentage';
    case Variant = 'variant';
    case Config = 'config';

    public function label(): string
    {
        return match ($this) {
            self::Boolean => 'Boolean',
            self::Percentage => 'Percentage Rollout',
            self::Variant => 'Variant',
            self::Config => 'Configuration',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Boolean => 'Simple on/off feature flag.',
            self::Percentage => 'Feature enabled for a percentage of users.',
            self::Variant => 'Feature with multiple variants for A/B testing.',
            self::Config => 'Feature that returns a configuration value.',
        };
    }

    public function requiresSubject(): bool
    {
        return match ($this) {
            self::Boolean => false,
            self::Percentage => true,
            self::Variant => true,
            self::Config => true,
        };
    }
}
