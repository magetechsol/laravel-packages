<?php

declare(strict_types=1);

namespace MageTech\SaaS\Database;

use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use MageTech\SaaS\Contracts\DatabaseStrategyContract;
use MageTech\SaaS\Models\Tenant;

class DatabasePerTenantStrategy implements DatabaseStrategyContract
{
    protected ?Tenant $currentTenant = null;

    protected string $connection = 'tenant';

    public function __construct(
        protected Repository $config,
    ) {
        $this->ensureTenantConnectionExists();
    }

    public function setTenant(Tenant $tenant): void
    {
        $this->currentTenant = $tenant;
    }

    public function createTenant(array $data): Tenant
    {
        $model = $this->config->get('mts-saas.model', Tenant::class);

        $tenant = $model::create($data);

        $this->createTenantDatabase($tenant);

        return $tenant;
    }

    public function deleteTenant(Tenant $tenant): void
    {
        $this->dropTenantDatabase($tenant);

        $tenant->delete();
    }

    public function getDatabaseName(Tenant $tenant): string
    {
        $prefix = $this->config->get('mts-saas.key_column', 'tenant_id');

        return "{$prefix}_{$tenant->getKey()}";
    }

    public function migrate(Tenant $tenant): void
    {
        $this->makeTenantCurrent($tenant);

        $database = $this->getDatabaseName($tenant);

        $path = $this->config->get('mts-saas.migrations.path');

        if ($path && is_dir($path)) {
            \Artisan::call('migrate', [
                '--database' => $this->connection,
                '--path' => $path,
                '--force' => true,
            ]);
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

        config(['database.connections.tenant.database' => config('database.connections.' . config('database.default') . '.database')]);
    }

    public function makeTenantCurrent(Tenant $tenant): void
    {
        $this->currentTenant = $tenant;

        $database = $this->getDatabaseName($tenant);

        config(["database.connections.{$this->connection}.database" => $database]);

        DB::purge($this->connection);
    }

    public function getTenantConnection(): string
    {
        return $this->connection;
    }

    protected function createTenantDatabase(Tenant $tenant): void
    {
        $database = $this->getDatabaseName($tenant);

        $defaultConnection = config('database.default');
        $defaultConfig = config("database.connections.{$defaultConnection}");

        DB::purge('mysql');

        $charset = $defaultConfig['charset'] ?? 'utf8mb4';
        $collation = $defaultConfig['collation'] ?? 'utf8mb4_unicode_ci';

        DB::statement("CREATE DATABASE `{$database}` CHARACTER SET {$charset} COLLATE {$collation}");
    }

    protected function dropTenantDatabase(Tenant $tenant): void
    {
        $database = $this->getDatabaseName($tenant);

        DB::purge('mysql');

        DB::statement("DROP DATABASE IF EXISTS `{$database}`");
    }

    protected function ensureTenantConnectionExists(): void
    {
        $defaultConnection = config('database.default');
        $defaultConfig = config("database.connections.{$defaultConnection}");

        if (! config("database.connections.{$this->connection}")) {
            config([
                "database.connections.{$this->connection}" => array_merge($defaultConfig, [
                    'database' => $defaultConfig['database'] ?? 'forge',
                    'prefix' => '',
                ]),
            ]);
        }
    }
}
