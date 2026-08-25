<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Enums;

enum FeatureFlagStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Archived => 'Archived',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
