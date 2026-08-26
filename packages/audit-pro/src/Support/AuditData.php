<?php

declare(strict_types=1);

namespace MageTech\Audit\Support;

final readonly class AuditData
{
    public function __construct(
        public string $event,
        public ?string $auditableType = null,
        public int|string|null $auditableId = null,
        public ?string $actorType = null,
        public int|string|null $actorId = null,
        public ?string $actorName = null,
        public ?string $actorEmail = null,
        public ?string $action = null,
        public ?string $description = null,
        public ?string $url = null,
        public ?string $method = null,
        public ?string $route = null,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
        public ?string $requestId = null,
        public ?string $sessionId = null,
        public ?array $oldValues = null,
        public ?array $newValues = null,
        public ?array $changedValues = null,
        public ?array $metadata = null,
        public ?array $tags = null,
        public int|string|null $tenantId = null,
        public ?string $batchUuid = null,
        public ?string $previousHash = null,
        public ?string $recordHash = null,
    ) {}

    public static function make(): self
    {
        return new self(event: 'custom');
    }

    public static function fromArray(array $data): self
    {
        return new self(
            event: $data['event'] ?? 'custom',
            auditableType: $data['auditable_type'] ?? null,
            auditableId: $data['auditable_id'] ?? null,
            actorType: $data['actor_type'] ?? null,
            actorId: $data['actor_id'] ?? null,
            actorName: $data['actor_name'] ?? null,
            actorEmail: $data['actor_email'] ?? null,
            action: $data['action'] ?? null,
            description: $data['description'] ?? null,
            url: $data['url'] ?? null,
            method: $data['method'] ?? null,
            route: $data['route'] ?? null,
            ipAddress: $data['ip_address'] ?? null,
            userAgent: $data['user_agent'] ?? null,
            requestId: $data['request_id'] ?? null,
            sessionId: $data['session_id'] ?? null,
            oldValues: $data['old_values'] ?? null,
            newValues: $data['new_values'] ?? null,
            changedValues: $data['changed_values'] ?? null,
            metadata: $data['metadata'] ?? null,
            tags: $data['tags'] ?? null,
            tenantId: $data['tenant_id'] ?? null,
            batchUuid: $data['batch_uuid'] ?? null,
            previousHash: $data['previous_hash'] ?? null,
            recordHash: $data['record_hash'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'event' => $this->event,
            'auditable_type' => $this->auditableType,
            'auditable_id' => $this->auditableId,
            'actor_type' => $this->actorType,
            'actor_id' => $this->actorId,
            'actor_name' => $this->actorName,
            'actor_email' => $this->actorEmail,
            'action' => $this->action,
            'description' => $this->description,
            'url' => $this->url,
            'method' => $this->method,
            'route' => $this->route,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'request_id' => $this->requestId,
            'session_id' => $this->sessionId,
            'old_values' => $this->oldValues,
            'new_values' => $this->newValues,
            'changed_values' => $this->changedValues,
            'metadata' => $this->metadata,
            'tags' => $this->tags,
            'tenant_id' => $this->tenantId,
            'batch_uuid' => $this->batchUuid,
            'previous_hash' => $this->previousHash,
            'record_hash' => $this->recordHash,
        ];
    }

    public function withHash(string $hash, ?string $previousHash = null): self
    {
        return new self(
            event: $this->event,
            auditableType: $this->auditableType,
            auditableId: $this->auditableId,
            actorType: $this->actorType,
            actorId: $this->actorId,
            actorName: $this->actorName,
            actorEmail: $this->actorEmail,
            action: $this->action,
            description: $this->description,
            url: $this->url,
            method: $this->method,
            route: $this->route,
            ipAddress: $this->ipAddress,
            userAgent: $this->userAgent,
            requestId: $this->requestId,
            sessionId: $this->sessionId,
            oldValues: $this->oldValues,
            newValues: $this->newValues,
            changedValues: $this->changedValues,
            metadata: $this->metadata,
            tags: $this->tags,
            tenantId: $this->tenantId,
            batchUuid: $this->batchUuid,
            previousHash: $previousHash,
            recordHash: $hash,
        );
    }

    public function withBatch(string $batchUuid): self
    {
        return new self(
            event: $this->event,
            auditableType: $this->auditableType,
            auditableId: $this->auditableId,
            actorType: $this->actorType,
            actorId: $this->actorId,
            actorName: $this->actorName,
            actorEmail: $this->actorEmail,
            action: $this->action,
            description: $this->description,
            url: $this->url,
            method: $this->method,
            route: $this->route,
            ipAddress: $this->ipAddress,
            userAgent: $this->userAgent,
            requestId: $this->requestId,
            sessionId: $this->sessionId,
            oldValues: $this->oldValues,
            newValues: $this->newValues,
            changedValues: $this->changedValues,
            metadata: $this->metadata,
            tags: $this->tags,
            tenantId: $this->tenantId,
            batchUuid: $batchUuid,
            previousHash: $this->previousHash,
            recordHash: $this->recordHash,
        );
    }
}
