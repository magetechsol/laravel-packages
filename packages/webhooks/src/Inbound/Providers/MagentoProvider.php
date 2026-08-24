<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Inbound\Providers;

use Illuminate\Http\Request;

class MagentoProvider extends AbstractProvider
{
    public function name(): string
    {
        return 'magento';
    }

    public function getSignatureHeader(): string
    {
        return 'X-Magento-Webhook-Signature';
    }

    public function extractEventName(Request $request): ?string
    {
        return $request->header('X-Magento-Event')
            ?? $request->input('event')
            ?? null;
    }

    public function extractIdempotencyKey(Request $request, array $payload): ?string
    {
        return $request->header('X-Magento-Webhook-Id')
            ?? $payload['id']
            ?? null;
    }
}
