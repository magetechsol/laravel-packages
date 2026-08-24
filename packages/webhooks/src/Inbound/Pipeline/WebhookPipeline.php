<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Inbound\Pipeline;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use MageTech\Webhooks\Events\WebhookFailed;
use MageTech\Webhooks\Events\WebhookProcessed;
use MageTech\Webhooks\Events\WebhookReceived;
use MageTech\Webhooks\Enums\WebhookStatus;
use MageTech\Webhooks\Models\Webhook;

class WebhookPipeline
{
    public function __construct(
        private readonly Authenticator $authenticator,
        private readonly Validator $validator,
        private readonly Persister $persister,
        private readonly Deduplicator $deduplicator,
        private readonly Processor $processor,
        private readonly Retrier $retrier,
    ) {}

    public function handle(Request $request, string $provider): Webhook
    {
        $startTime = microtime(true);

        $providerInstance = $this->authenticator->authenticate($request, $provider);

        $payload = $this->validator->validate($request, $provider);

        $eventName = $providerInstance->extractEventName($request);
        $idempotencyKey = $providerInstance->extractIdempotencyKey($request, $payload);
        $signature = $request->header($providerInstance->getSignatureHeader());

        $webhook = $this->persister->persist(
            request: $request,
            payload: $payload,
            provider: $provider,
            eventName: $eventName,
            idempotencyKey: $idempotencyKey,
            signature: $signature,
        );

        Event::dispatch(new WebhookReceived($webhook));

        $this->deduplicator->ensureUnique($webhook);

        try {
            $webhook->markAsProcessing();

            $this->processor->process($webhook);

            $duration = (int) ((microtime(true) - $startTime) * 1000);

            $webhook->markAsProcessed();

            $this->persister->recordAttempt(
                webhook: $webhook,
                attemptNumber: $webhook->attempts + 1,
                status: 'success',
                durationMs: $duration,
            );

            Event::dispatch(new WebhookProcessed($webhook));

            if (config('mts-webhooks.logging.enabled', true)) {
                Log::info('Webhook processed successfully', [
                    'webhook_id' => $webhook->id,
                    'provider' => $provider,
                    'event' => $eventName,
                    'duration_ms' => $duration,
                ]);
            }
        } catch (\Throwable $exception) {
            $duration = (int) ((microtime(true) - $startTime) * 1000);

            Event::dispatch(new WebhookFailed($webhook, $exception));

            $this->retrier->handle($webhook, $exception);
        }

        return $webhook;
    }
}
