<?php

declare(strict_types=1);

namespace MageTech\SaaS\Contracts;

use MageTech\SaaS\Models\Tenant;

interface TenantManagerContract
{
    public function identify(): ?Tenant;

    public function setTenant(Tenant $tenant): void;

    public function getTenant(): ?Tenant;

    public function getTenantId(): ?string;

    public function getTenantKey(): ?string;

    public function isActive(): bool;

    public function isSuspended(): bool;

    public function create(array $data): Tenant;

    public function activate(Tenant $tenant): void;

    public function suspend(Tenant $tenant, ?string $reason = null): void;

    public function delete(Tenant $tenant): void;

    public function migrate(Tenant $tenant): void;

    public function migrateAll(): void;

    public function reset(): void;
}
