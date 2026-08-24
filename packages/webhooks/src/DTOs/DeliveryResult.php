<?php

declare(strict_types=1);

namespace MageTech\Webhooks\DTOs;

final readonly class DeliveryResult
{
    public function __construct(
        public bool $success,
        public int $responseCode,
        public string $responseBody,
        public ?string $error = null,
        public ?int $deliveryId = null,
    ) {}

    public static function success(int $responseCode, string $responseBody, ?int $deliveryId = null): static
    {
        return new static(
            success: true,
            responseCode: $responseCode,
            responseBody: $responseBody,
            deliveryId: $deliveryId,
        );
    }

    public static function failure(int $responseCode, string $responseBody, string $error, ?int $deliveryId = null): static
    {
        return new static(
            success: false,
            responseCode: $responseCode,
            responseBody: $responseBody,
            error: $error,
            deliveryId: $deliveryId,
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'response_code' => $this->responseCode,
            'response_body' => $this->responseBody,
            'error' => $this->error,
            'delivery_id' => $this->deliveryId,
        ];
    }
}
