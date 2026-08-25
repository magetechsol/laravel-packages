<?php

declare(strict_types=1);

namespace MageTech\SaaS\Exceptions;

use RuntimeException;

class TenantDatabaseException extends RuntimeException
{
    public static function creationFailed(string $database, string $reason): static
    {
        return new static("Failed to create database [{$database}]: {$reason}");
    }

    public static function migrationFailed(string $tenant, string $reason): static
    {
        return new static("Migration failed for tenant [{$tenant}]: {$reason}");
    }

    public static function connectionFailed(string $database): static
    {
        return new static("Could not connect to tenant database [{$database}].");
    }
}
