<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Inbound\Providers;

use Illuminate\Http\Request;

class GenericProvider extends AbstractProvider
{
    public function name(): string
    {
        return 'generic';
    }

    public function getSignatureHeader(): string
    {
        return 'X-Webhook-Signature';
    }

    public function extractEventName(Request $request): ?string
    {
        return $request->header('X-Webhook-Event')
            ?? $request->input('event')
            ?? $request->input('type')
            ?? null;
    }

    public function extractIdempotencyKey(Request $request, array $payload): ?string
    {
        return $request->header('X-Webhook-Id')
            ?? $payload['id']
            ?? $payload['event_id']
            ?? $payload['webhook_id']
            ?? null;
    }
}
