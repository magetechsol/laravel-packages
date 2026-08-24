<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit\Enums;

enum ErrorSeverity: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    /**
     * Get the label for this severity level.
     */
    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::CRITICAL => 'Critical',
        };
    }

    /**
     * Get the description for this severity level.
     */
    public function description(): string
    {
        return match ($this) {
            self::LOW => 'Minor issues that do not affect functionality.',
            self::MEDIUM => 'Issues that may affect functionality under certain conditions.',
            self::HIGH => 'Issues that significantly impact functionality.',
            self::CRITICAL => 'Critical issues that prevent functionality entirely.',
        };
    }

    /**
     * Check if this severity should be logged.
     */
    public function shouldLog(): bool
    {
        return match ($this) {
            self::LOW => false,
            self::MEDIUM, self::HIGH, self::CRITICAL => true,
        };
    }

    /**
     * Check if this severity should alert administrators.
     */
    public function shouldAlert(): bool
    {
        return match ($this) {
            self::LOW, self::MEDIUM => false,
            self::HIGH, self::CRITICAL => true,
        };
    }
}
