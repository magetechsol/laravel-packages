<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Inbound\Providers;

use Illuminate\Http\Request;

class ShopifyProvider extends AbstractProvider
{
    public function name(): string
    {
        return 'shopify';
    }

    public function getSignatureHeader(): string
    {
        return 'X-Shopify-Hmac-Sha256';
    }

    public function verifySignature(Request $request, string $secret): bool
    {
        $signature = $request->header('X-Shopify-Hmac-Sha256');

        if ($signature === null || $signature === '') {
            return false;
        }

        $payload = $request->getContent();
        $computed = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        return hash_equals($computed, $signature);
    }

    public function extractEventName(Request $request): ?string
    {
        return $request->header('X-Shopify-Topic');
    }

    public function extractIdempotencyKey(Request $request, array $payload): ?string
    {
        return $request->header('X-Shopify-Webhook-Id');
    }
}
