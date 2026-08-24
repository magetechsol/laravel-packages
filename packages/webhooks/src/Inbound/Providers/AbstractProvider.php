<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Inbound\Providers;

use Illuminate\Http\Request;
use MageTech\Webhooks\Contracts\WebhookProviderContract;

abstract class AbstractProvider implements WebhookProviderContract
{
    abstract public function name(): string;

    abstract public function getSignatureHeader(): string;

    public function verifySignature(Request $request, string $secret): bool
    {
        $signature = $request->header($this->getSignatureHeader());

        if ($signature === null || $signature === '') {
            return false;
        }

        $payload = $request->getContent();
        $expected = $this->computeSignature($payload, $secret);

        return hash_equals($expected, $signature);
    }

    public function extractEventName(Request $request): ?string
    {
        return $request->header('X-Webhook-Event')
            ?? $request->input('event')
            ?? $request->input('type');
    }

    public function extractIdempotencyKey(Request $request, array $payload): ?string
    {
        return $request->header('X-Webhook-Id')
            ?? $payload['id']
            ?? $payload['event_id']
            ?? null;
    }

    public function getTimestampFromRequest(Request $request): ?int
    {
        $timestamp = $request->header('X-Webhook-Timestamp')
            ?? $request->header('X-Timestamp');

        return $timestamp ? (int) $timestamp : null;
    }

    protected function computeSignature(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }
}
