<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Inbound\Providers;

use Illuminate\Http\Request;

class RazorpayProvider extends AbstractProvider
{
    public function name(): string
    {
        return 'razorpay';
    }

    public function getSignatureHeader(): string
    {
        return 'X-Razorpay-Signature';
    }

    public function verifySignature(Request $request, string $secret): bool
    {
        $signature = $request->header('X-Razorpay-Signature');

        if ($signature === null || $signature === '') {
            return false;
        }

        $payload = $request->getContent();
        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }

    public function extractEventName(Request $request): ?string
    {
        $payload = $request->input('payload.payment', [])
            ?? $request->input('payload.order', [])
            ?? [];

        return $payload['event'] ?? $request->input('event') ?? null;
    }

    public function extractIdempotencyKey(Request $request, array $payload): ?string
    {
        return $payload['payload']['payment']['entity']['id']
            ?? $payload['payload']['order']['entity']['id']
            ?? $payload['id']
            ?? null;
    }
}
