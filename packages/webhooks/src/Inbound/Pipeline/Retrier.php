<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Inbound\Pipeline;

use Illuminate\Support\Facades\Log;
use MageTech\Webhooks\Models\Webhook;
use MageTech\Webhooks\Support\RetryStrategy;

class Retrier
{
    public function handle(Webhook $webhook, \Throwable $exception): void
    {
        $webhook->incrementAttempts();

        $attemptNumber = $webhook->attempts;

        app(Persister::class)->recordAttempt(
            webhook: $webhook,
            attemptNumber: $attemptNumber,
            status: 'failed',
            error: $exception->getMessage(),
        );

        if ($webhook->attempts >= $webhook->max_attempts) {
            app(DeadLetterHandler::class)->handle($webhook, $exception);

            return;
        }

        $nextRetryAt = app(RetryStrategy::class)->calculateNextRetry(
            attempt: $webhook->attempts,
            baseDelay: config('mts-webhooks.retry.base_delay', 60),
            maxDelay: config('mts-webhooks.retry.max_delay', 3600),
            multiplier: config('mts-webhooks.retry.backoff_multiplier', 2),
        );

        $webhook->markAsFailed($exception->getMessage());
        $webhook->scheduleRetry($nextRetryAt);

        if (config('mts-webhooks.logging.enabled', true)) {
            Log::info('Webhook scheduled for retry', [
                'webhook_id' => $webhook->id,
                'attempt' => $webhook->attempts,
                'max_attempts' => $webhook->max_attempts,
                'next_retry_at' => $nextRetryAt->toIso8601String(),
            ]);
        }
    }
}
