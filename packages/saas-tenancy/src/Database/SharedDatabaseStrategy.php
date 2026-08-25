<?php

declare(strict_types=1);

namespace MageTech\SaaS\Database;

use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MageTech\SaaS\Contracts\DatabaseStrategyContract;
use MageTech\SaaS\Models\Tenant;

class SharedDatabaseStrategy implements DatabaseStrategyContract
{
    protected ?Tenant $currentTenant = null;

    public function __construct(
        protected Repository $config,
    ) {}

    public function setTenant(Tenant $tenant): void
    {
        $this->currentTenant = $tenant;
    }

    public function createTenant(array $data): Tenant
    {
        $model = $this->config->get('mts-saas.model', Tenant::class);

        return $model::create($data);
    }

    public function deleteTenant(Tenant $tenant): void
    {
        $this->deleteTenantData($tenant);

        $tenant->delete();
    }

    public function getDatabaseName(Tenant $tenant): string
    {
        return config('database.default');
    }

    public function migrate(Tenant $tenant): void
    {
        $this->makeTenantCurrent($tenant);

        $path = $this->config->get('mts-saas.migrations.path');
        $connection = $this->config->get('mts-saas.migrations.connection');

        if ($path && is_dir($path)) {
            \Artisan::call('migrate', [
                '--path' => $path,
                '--force' => true,
            ] + ($connection ? ['--database' => $connection] : []));
        }
    }

    public function migrateAll(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->migrate($tenant);
        }
    }

    public function getTenants(): Collection
    {
        return Tenant::all();
    }

    public function reset(): void
    {
        $this->currentTenant = null;

        $this->resetQueryScopes();
    }

    public function makeTenantCurrent(Tenant $tenant): void
    {
        $this->currentTenant = $tenant;

        $this->applyQueryScope($tenant);
    }

    public function getTenantConnection(): string
    {
        return config('database.default');
    }

    protected function applyQueryScope(Tenant $tenant): void
    {
        // Query scoping is handled via Eloquent TenantScope (BelongsToTenant trait).
        // This method provides a hook for additional raw query scoping if needed.
    }

    protected function resetQueryScopes(): void
    {
        // Query scope reset is handled via Eloquent TenantScope.
        // This method provides a hook for additional raw query scope cleanup if needed.
    }

    protected function deleteTenantData(Tenant $tenant): void
    {
        $column = $this->config->get('mts-saas.key_column', 'tenant_id');
        $tenantKey = $tenant->getKey();

        $scopes = $this->config->get('mts-saas.scoped_tables', []);

        foreach ($scopes as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                DB::table($table)->where($column, $tenantKey)->delete();
            }
        }
    }
}
