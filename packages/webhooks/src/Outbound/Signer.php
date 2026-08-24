<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Outbound;

final class Signer
{
    public function sign(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }

    public function verify(string $payload, string $secret, string $signature): bool
    {
        $expected = $this->sign($payload, $secret);

        return hash_equals($expected, $signature);
    }

    public function generateHeader(string $payload, string $secret): array
    {
        $signature = $this->sign($payload, $secret);
        $timestamp = time();

        return [
            'X-Webhook-Signature' => $signature,
            'X-Webhook-Timestamp' => (string) $timestamp,
            'X-Webhook-Signed-Payload' => hash('sha256', $payload),
        ];
    }
}
