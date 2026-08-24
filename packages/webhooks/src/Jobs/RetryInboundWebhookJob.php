<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use MageTech\Webhooks\Enums\WebhookStatus;
use MageTech\Webhooks\Inbound\Pipeline\DeadLetterHandler;
use MageTech\Webhooks\Inbound\Pipeline\Persister;
use MageTech\Webhooks\Inbound\Pipeline\Processor;
use MageTech\Webhooks\Models\Webhook;

class RetryInboundWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout;

    public int $tries;

    public function __construct(public Webhook $webhook)
    {
        $this->timeout = config('mts-webhooks.processing.timeout', 30);
        $this->tries = 1;
        $this->queue = config('mts-webhooks.processing.queue', 'default');
        $this->connection = config('mts-webhooks.processing.connection');
    }

    public function handle(): void
    {
        $this->webhook->refresh();

        if (! $this->webhook->canRetry()) {
            return;
        }

        $startTime = microtime(true);

        $this->webhook->markAsProcessing();

        try {
            app(Processor::class)->process($this->webhook);

            $duration = (int) ((microtime(true) - $startTime) * 1000);

            $this->webhook->markAsProcessed();

            app(Persister::class)->recordAttempt(
                webhook: $this->webhook,
                attemptNumber: $this->webhook->attempts + 1,
                status: 'success',
                durationMs: $duration,
            );

            if (config('mts-webhooks.logging.enabled', true)) {
                Log::info('Webhook retry succeeded', [
                    'webhook_id' => $this->webhook->id,
                    'attempt' => $this->webhook->attempts,
                    'duration_ms' => $duration,
                ]);
            }
        } catch (\Throwable $exception) {
            $this->webhook->incrementAttempts();

            app(Persister::class)->recordAttempt(
                webhook: $this->webhook,
                attemptNumber: $this->webhook->attempts,
                status: 'failed',
                error: $exception->getMessage(),
            );

            if ($this->webhook->attempts >= $this->webhook->max_attempts) {
                app(DeadLetterHandler::class)->handle($this->webhook, $exception);
            } else {
                $this->webhook->markAsFailed($exception->getMessage());

                $nextRetryAt = now()->addSeconds(
                    config('mts-webhooks.retry.base_delay', 60) * pow(
                        config('mts-webhooks.retry.backoff_multiplier', 2),
                        $this->webhook->attempts - 1,
                    ),
                );

                $this->webhook->scheduleRetry($nextRetryAt);
            }

            throw $exception;
        }
    }

    public function failed(\Throwable $exception): void
    {
        if (config('mts-webhooks.logging.enabled', true)) {
            Log::error('Webhook retry job failed', [
                'webhook_id' => $this->webhook->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
