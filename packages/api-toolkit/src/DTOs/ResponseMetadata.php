<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit\DTOs;

readonly class ResponseMetadata
{
    public function __construct(
        public ?string $requestId = null,
        public ?string $correlationId = null,
        public ?string $apiVersion = null,
        public ?string $timestamp = null,
        public ?array $rateLimit = null,
        public ?PaginationData $pagination = null,
    ) {}

    /**
     * Create metadata from request attributes.
     *
     * @param  array  $attributes
     */
    public static function fromRequest(array $attributes): static
    {
        return new static(
            requestId: $attributes['request_id'] ?? null,
            correlationId: $attributes['correlation_id'] ?? null,
            apiVersion: $attributes['api_version'] ?? null,
            timestamp: $attributes['timestamp'] ?? now()->toIso8601String(),
        );
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $metadata = [];

        if ($this->requestId !== null) {
            $metadata['request_id'] = $this->requestId;
        }

        if ($this->correlationId !== null) {
            $metadata['correlation_id'] = $this->correlationId;
        }

        if ($this->apiVersion !== null) {
            $metadata['api_version'] = $this->apiVersion;
        }

        if ($this->timestamp !== null) {
            $metadata['timestamp'] = $this->timestamp;
        }

        if ($this->rateLimit !== null) {
            $metadata['rate_limit'] = $this->rateLimit;
        }

        if ($this->pagination !== null) {
            $metadata['pagination'] = $this->pagination->toArray();
        }

        return $metadata;
    }
}
