<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Inbound\Providers;

use Illuminate\Http\Request;

class StripeProvider extends AbstractProvider
{
    public function name(): string
    {
        return 'stripe';
    }

    public function getSignatureHeader(): string
    {
        return 'Stripe-Signature';
    }

    public function verifySignature(Request $request, string $secret): bool
    {
        $signatureHeader = $request->header('Stripe-Signature');

        if ($signatureHeader === null || $signatureHeader === '') {
            return false;
        }

        $payload = $request->getContent();
        $signatureParts = $this->parseSignatureHeader($signatureHeader);

        if ($signatureParts === null) {
            return false;
        }

        $signedPayload = $signatureParts['timestamp'] . '.' . $payload;
        $expected = hash_hmac('sha256', $signedPayload, $secret);

        return hash_equals($expected, $signatureParts['signature']);
    }

    public function getTimestampFromRequest(Request $request): ?int
    {
        $signatureHeader = $request->header('Stripe-Signature');

        if ($signatureHeader === null) {
            return null;
        }

        $signatureParts = $this->parseSignatureHeader($signatureHeader);

        return $signatureParts !== null ? (int) $signatureParts['timestamp'] : null;
    }

    public function extractEventName(Request $request): ?string
    {
        return $request->input('type');
    }

    public function extractIdempotencyKey(Request $request, array $payload): ?string
    {
        return $payload['id'] ?? null;
    }

    private function parseSignatureHeader(string $header): ?array
    {
        $elements = [];
        $pairs = explode(',', $header);

        foreach ($pairs as $pair) {
            $parts = explode('=', $pair, 2);

            if (count($parts) === 2) {
                $elements[trim($parts[0])] = trim($parts[1]);
            }
        }

        if (! isset($elements['t'], $elements['v1'])) {
            return null;
        }

        return [
            'timestamp' => $elements['t'],
            'signature' => $elements['v1'],
        ];
    }
}
