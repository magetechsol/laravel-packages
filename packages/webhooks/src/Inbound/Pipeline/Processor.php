<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Inbound\Pipeline;

use Illuminate\Support\Facades\Log;
use MageTech\Webhooks\Contracts\WebhookHandlerContract;
use MageTech\Webhooks\Models\Webhook;

class Processor
{
    public function process(Webhook $webhook): void
    {
        $handlerClass = $this->resolveHandler($webhook->event, $webhook->provider);

        if ($handlerClass === null) {
            if (config('mts-webhooks.logging.enabled', true)) {
                Log::info('No handler registered for webhook event', [
                    'webhook_id' => $webhook->id,
                    'event' => $webhook->event,
                    'provider' => $webhook->provider,
                ]);
            }

            return;
        }

        /** @var WebhookHandlerContract $handler */
        $handler = app($handlerClass);

        $handler->handle(
            payload: $webhook->payload,
            headers: $webhook->headers,
            event: $webhook->event,
            provider: $webhook->provider,
        );
    }

    private function resolveHandler(string $event, string $provider): ?string
    {
        $handlerMap = config('mts-webhooks.processing.handler_map', []);

        if (isset($handlerMap[$provider . '.' . $event])) {
            return $handlerMap[$provider . '.' . $event];
        }

        if (isset($handlerMap[$event])) {
            return $handlerMap[$event];
        }

        return null;
    }
}
