<?php

declare(strict_types=1);

namespace MageTech\DevTools\Enums;

enum HealthStatus: string
{
    case Healthy = 'healthy';
    case Warning = 'warning';
    case Critical = 'critical';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Healthy => 'Healthy',
            self::Warning => 'Warning',
            self::Critical => 'Critical',
            self::Unknown => 'Unknown',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Healthy => 'green',
            self::Warning => 'yellow',
            self::Critical => 'red',
            self::Unknown => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Healthy => '✓',
            self::Warning => '⚠',
            self::Critical => '✗',
            self::Unknown => '?',
        };
    }
}
