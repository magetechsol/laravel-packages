<?php

declare(strict_types=1);

namespace MageTech\Webhooks\DTOs;

final readonly class WebhookPayload
{
    public function __construct(
        public string $provider,
        public string $event,
        public array $payload,
        public array $headers,
        public ?string $signature = null,
        public ?string $idempotencyKey = null,
        public ?string $requestId = null,
        public ?string $sourceIp = null,
    ) {}

    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'event' => $this->event,
            'payload' => $this->payload,
            'headers' => $this->headers,
            'signature' => $this->signature,
            'idempotency_key' => $this->idempotencyKey,
            'request_id' => $this->requestId,
            'source_ip' => $this->sourceIp,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            provider: $data['provider'] ?? 'unknown',
            event: $data['event'] ?? 'unknown',
            payload: $data['payload'] ?? [],
            headers: $data['headers'] ?? [],
            signature: $data['signature'] ?? null,
            idempotencyKey: $data['idempotency_key'] ?? null,
            requestId: $data['request_id'] ?? null,
            sourceIp: $data['source_ip'] ?? null,
        );
    }
}
