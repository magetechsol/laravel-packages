<?php

declare(strict_types=1);

namespace MageTech\Workflow\Enums;

enum RetryBackoff: string
{
    case Fixed = 'fixed';
    case Linear = 'linear';
    case Exponential = 'exponential';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed Delay',
            self::Linear => 'Linear Backoff',
            self::Exponential => 'Exponential Backoff',
        };
    }

    public function calculate(int $attempt, int $baseDelay, int $maxDelay = 3600): int
    {
        $delay = match ($this) {
            self::Fixed => $baseDelay,
            self::Linear => $baseDelay * $attempt,
            self::Exponential => $baseDelay * (int) pow(2, $attempt - 1),
        };

        return min($delay, $maxDelay);
    }
}
