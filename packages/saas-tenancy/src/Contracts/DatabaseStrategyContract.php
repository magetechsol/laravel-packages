<?php

declare(strict_types=1);

namespace MageTech\SaaS\Contracts;

use Illuminate\Support\Collection;
use MageTech\SaaS\Models\Tenant;

interface DatabaseStrategyContract
{
    public function setTenant(Tenant $tenant): void;

    public function createTenant(array $data): Tenant;

    public function deleteTenant(Tenant $tenant): void;

    public function getDatabaseName(Tenant $tenant): string;

    public function migrate(Tenant $tenant): void;

    public function migrateAll(): void;

    public function getTenants(): Collection;

    public function reset(): void;

    public function makeTenantCurrent(Tenant $tenant): void;

    public function getTenantConnection(): string;
}
