<?php

declare(strict_types=1);

namespace MageTech\Workflow\Concerns;

use MageTech\Workflow\Enums\RetryBackoff;

trait HasRetryLogic
{
    /**
     * Get the retry delay for a given attempt number.
     */
    public function getRetryDelay(int $attempt): int
    {
        $backoff = RetryBackoff::from(config('mts-workflow.retry.backoff', 'exponential'));
        $baseDelay = config('mts-workflow.retry.base_delay', 60);
        $maxDelay = config('mts-workflow.retry.max_delay', 3600);

        return $backoff->calculate($attempt, $baseDelay, $maxDelay);
    }

    /**
     * Check if the given attempt number is within retry limits.
     */
    public function canRetry(int $attempt): bool
    {
        return $attempt < config('mts-workflow.retry.max_attempts', 3);
    }
}
