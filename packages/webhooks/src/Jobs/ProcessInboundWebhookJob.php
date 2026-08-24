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
use MageTech\Webhooks\Inbound\Pipeline\Retrier;
use MageTech\Webhooks\Models\Webhook;
use MageTech\Webhooks\Support\RetryStrategy;

class ProcessInboundWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout;

    public int $tries;

    public function __construct(public Webhook $webhook)
    {
        $this->timeout = config('mts-webhooks.processing.timeout', 30);
        $this->tries = config('mts-webhooks.retry.max_attempts', 5);
        $this->queue = config('mts-webhooks.processing.queue', 'default');
        $this->connection = config('mts-webhooks.processing.connection');
    }

    public function handle(): void
    {
        $this->webhook->refresh();

        if ($this->webhook->status === WebhookStatus::Processed) {
            return;
        }

        if ($this->webhook->status === WebhookStatus::Dead) {
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
                Log::info('Webhook processed via queue job', [
                    'webhook_id' => $this->webhook->id,
                    'duration_ms' => $duration,
                ]);
            }
        } catch (\Throwable $exception) {
            app(Retrier::class)->handle($this->webhook, $exception);

            throw $exception;
        }
    }

    public function failed(\Throwable $exception): void
    {
        if (config('mts-webhooks.logging.enabled', true)) {
            Log::error('Webhook queue job failed permanently', [
                'webhook_id' => $this->webhook->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
