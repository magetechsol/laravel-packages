<?php

declare(strict_types=1);

namespace MageTech\Audit\Exceptions;

use RuntimeException;

class AuditException extends RuntimeException
{
    public static function integrityVerificationFailed(string $uuid): static
    {
        return new static("Integrity verification failed for audit record: {$uuid}");
    }

    public static function storeFailure(string $reason): static
    {
        return new static("Failed to store audit record: {$reason}");
    }

    public static function invalidConfiguration(string $key): static
    {
        return new static("Invalid audit configuration key: {$key}");
    }
}
