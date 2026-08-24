<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Outbound;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MageTech\Webhooks\Enums\DeliveryStatus;
use MageTech\Webhooks\Models\WebhookDelivery;

class DeliveryTracker
{
    public function recordSuccess(WebhookDelivery $delivery, int $responseCode, string $responseBody): void
    {
        $delivery->markAsSuccess($responseCode, $responseBody);

        if (config('mts-webhooks.logging.enabled', true)) {
            Log::info('Outbound webhook delivered successfully', [
                'delivery_id' => $delivery->id,
                'event_name' => $delivery->event_name,
                'url' => $delivery->url,
                'response_code' => $responseCode,
            ]);
        }
    }

    public function recordFailure(WebhookDelivery $delivery, int $responseCode, string $responseBody, string $error): void
    {
        $delivery->incrementAttempts();

        if ($delivery->attempts >= $delivery->max_attempts) {
            $delivery->markAsDead($error);

            return;
        }

        $delivery->markAsFailed($responseCode, $responseBody, $error);

        $retryDelay = config('mts-webhooks.outbound.retry_delay', 60);
        $nextRetryAt = now()->addSeconds($retryDelay * $delivery->attempts);

        $delivery->scheduleRetry($nextRetryAt);

        if (config('mts-webhooks.logging.enabled', true)) {
            Log::warning('Outbound webhook delivery failed, scheduled for retry', [
                'delivery_id' => $delivery->id,
                'event_name' => $delivery->event_name,
                'url' => $delivery->url,
                'attempt' => $delivery->attempts,
                'next_retry_at' => $nextRetryAt->toIso8601String(),
            ]);
        }
    }

    public function deliver(WebhookDelivery $delivery): void
    {
        $payload = json_encode($delivery->payload, JSON_THROW_ON_ERROR);

        $headers = array_merge(
            $delivery->headers ?? [],
            ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        );

        try {
            $response = Http::withHeaders($headers)
                ->timeout(config('mts-webhooks.outbound.timeout', 30))
                ->post($delivery->url, $delivery->payload);

            if ($response->successful()) {
                $this->recordSuccess(
                    $delivery,
                    $response->status(),
                    $response->body(),
                );
            } else {
                $this->recordFailure(
                    $delivery,
                    $response->status(),
                    $response->body(),
                    'HTTP ' . $response->status(),
                );
            }
        } catch (\Throwable $e) {
            $this->recordFailure(
                $delivery,
                0,
                '',
                $e->getMessage(),
            );
        }
    }
}
