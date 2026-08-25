<?php

declare(strict_types=1);

namespace MageTech\Workflow\DTOs;

readonly class AuditContext
{
    public function __construct(
        public ?int $actorId = null,
        public ?string $actorType = null,
        public ?string $requestId = null,
        public ?string $ipAddress = null,
        public ?array $metadata = null,
    ) {}

    public function toArray(): array
    {
        return [
            'actor_id' => $this->actorId,
            'actor_type' => $this->actorType,
            'request_id' => $this->requestId,
            'ip_address' => $this->ipAddress,
            'metadata' => $this->metadata,
        ];
    }
}
