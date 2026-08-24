<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Support;

use Carbon\Carbon;

final class RetryStrategy
{
    public function calculateNextRetry(
        int $attempt,
        int $baseDelay = 60,
        int $maxDelay = 3600,
        float $multiplier = 2.0,
    ): Carbon {
        $delay = $baseDelay * pow($multiplier, $attempt - 1);

        $delay = min($delay, $maxDelay);

        $jitter = $delay * (mt_rand(-10, 10) / 100);
        $delay = max(1, $delay + $jitter);

        return Carbon::now()->addSeconds((int) $delay);
    }

    public function calculateDelay(
        int $attempt,
        int $baseDelay = 60,
        int $maxDelay = 3600,
        float $multiplier = 2.0,
    ): int {
        $delay = $baseDelay * pow($multiplier, $attempt - 1);

        return (int) min($delay, $maxDelay);
    }
}
