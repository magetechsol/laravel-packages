<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Inbound\Pipeline;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use MageTech\Webhooks\Events\WebhookDeadLettered;
use MageTech\Webhooks\Models\Webhook;

class DeadLetterHandler
{
    public function handle(Webhook $webhook, \Throwable $exception): void
    {
        $webhook->markAsDead($exception->getMessage());

        app(Persister::class)->recordAttempt(
            webhook: $webhook,
            attemptNumber: $webhook->attempts,
            status: 'failed',
            error: 'Dead letter: ' . $exception->getMessage(),
        );

        if (config('mts-webhooks.logging.enabled', true)) {
            Log::error('Webhook moved to dead letter queue', [
                'webhook_id' => $webhook->id,
                'provider' => $webhook->provider,
                'event' => $webhook->event,
                'attempts' => $webhook->attempts,
                'error' => $exception->getMessage(),
            ]);
        }

        Event::dispatch(new WebhookDeadLettered($webhook));

        $customEventClass = config('mts-webhooks.dead_letter.event');

        if ($customEventClass !== null) {
            Event::dispatch(new $customEventClass($webhook));
        }
    }
}
