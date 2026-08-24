<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Inbound\Pipeline;

use Illuminate\Http\Request;
use MageTech\Webhooks\Enums\WebhookStatus;
use MageTech\Webhooks\Models\Webhook;
use MageTech\Webhooks\Models\WebhookAttempt;
use MageTech\Webhooks\Support\SensitiveDataMasker;

class Persister
{
    public function persist(
        Request $request,
        array $payload,
        string $provider,
        ?string $eventName,
        ?string $idempotencyKey,
        ?string $signature,
    ): Webhook {
        $maskedPayload = app(SensitiveDataMasker::class)->mask($payload);

        $webhook = Webhook::create([
            'provider' => $provider,
            'event' => $eventName ?? 'unknown',
            'signature' => $signature,
            'payload' => $maskedPayload,
            'headers' => $this->sanitizeHeaders($request->headers->all()),
            'status' => WebhookStatus::Pending,
            'attempts' => 0,
            'max_attempts' => config('mts-webhooks.retry.max_attempts', 5),
            'idempotency_key' => $idempotencyKey,
            'request_id' => $request->header('X-Request-ID'),
            'source_ip' => $request->ip(),
        ]);

        $webhook->attempts()->create([
            'attempt_number' => 1,
            'status' => 'pending',
            'payload' => $maskedPayload,
        ]);

        return $webhook;
    }

    public function recordAttempt(
        Webhook $webhook,
        int $attemptNumber,
        string $status,
        ?string $error = null,
        ?int $durationMs = null,
    ): WebhookAttempt {
        return $webhook->attempts()->create([
            'attempt_number' => $attemptNumber,
            'status' => $status,
            'error' => $error,
            'duration_ms' => $durationMs,
        ]);
    }

    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'set-cookie', 'x-api-key'];

        $sanitized = [];

        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveHeaders, true)) {
                $sanitized[$key] = '[REDACTED]';
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
