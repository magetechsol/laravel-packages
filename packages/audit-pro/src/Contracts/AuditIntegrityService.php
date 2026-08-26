<?php

declare(strict_types=1);

namespace MageTech\Audit\Contracts;

interface AuditIntegrityService
{
    public function generateHash(array $data, ?string $previousHash = null): string;

    public function verifyHash(array $data, string $hash, ?string $previousHash = null): bool;

    public function getAlgorithm(): string;
}
