<?php

declare(strict_types=1);

namespace MageTech\Workflow\Support;

use MageTech\Workflow\Enums\RetryBackoff;

class RetryStrategy
{
    /**
     * Calculate the delay until the next retry attempt.
     */
    public function calculateNextRetry(
        int $currentAttempts,
        RetryBackoff $backoff,
        int $baseDelay = 60,
        int $maxDelay = 3600,
    ): \DateTimeInterface {
        $delay = $backoff->calculate($currentAttempts + 1, $baseDelay, $maxDelay);

        return now()->addSeconds($delay);
    }

    /**
     * Calculate the delay for a specific attempt.
     */
    public function calculateDelay(
        int $attempt,
        RetryBackoff $backoff,
        int $baseDelay = 60,
        int $maxDelay = 3600,
    ): int {
        return $backoff->calculate($attempt, $baseDelay, $maxDelay);
    }
}
