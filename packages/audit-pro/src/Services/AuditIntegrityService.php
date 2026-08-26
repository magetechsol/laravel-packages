<?php

declare(strict_types=1);

namespace MageTech\Audit\Services;

use MageTech\Audit\Contracts\AuditIntegrityService;

class AuditIntegrityService implements AuditIntegrityService
{
    protected string $algorithm;

    public function __construct()
    {
        $this->algorithm = config('audit.integrity.algorithm', 'sha256');
    }

    public function generateHash(array $data, ?string $previousHash = null): string
    {
        $payload = $this->buildPayload($data, $previousHash);

        return hash($this->algorithm, $payload);
    }

    public function verifyHash(array $data, string $hash, ?string $previousHash = null): bool
    {
        $expected = $this->generateHash($data, $previousHash);

        return hash_equals($expected, $hash);
    }

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    protected function buildPayload(array $data, ?string $previousHash): string
    {
        $fields = [
            'event' => $data['event'] ?? null,
            'auditable_type' => $data['auditable_type'] ?? null,
            'auditable_id' => $data['auditable_id'] ?? null,
            'actor_type' => $data['actor_type'] ?? null,
            'actor_id' => $data['actor_id'] ?? null,
            'action' => $data['action'] ?? null,
            'old_values' => $this->serializeValue($data['old_values'] ?? null),
            'new_values' => $this->serializeValue($data['new_values'] ?? null),
            'tenant_id' => $data['tenant_id'] ?? null,
            'created_at' => $data['created_at'] ?? null,
            'previous_hash' => $previousHash,
        ];

        return json_encode($fields, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }

    protected function serializeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_SORT_KEYS);
        }

        return (string) $value;
    }
}
