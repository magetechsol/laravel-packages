<?php

declare(strict_types=1);

namespace MageTech\SaaS\Console;

use Illuminate\Console\Command;
use MageTech\SaaS\Models\Tenant;
use MageTech\SaaS\Support\Facades\Tenant as TenantFacade;

class MigrateTenantCommand extends Command
{
    protected $signature = 'mts:saas:migrate-tenant
        {identifier? : Tenant ID, slug, or domain (all tenants if omitted)}
        {--fresh : Run fresh migration}';

    protected $description = 'Run migrations for a specific tenant or all tenants';

    public function handle(): int
    {
        $identifier = $this->argument('identifier');

        if ($identifier) {
            return $this->migrateSingle($identifier);
        }

        return $this->migrateAll();
    }

    protected function migrateSingle(string $identifier): int
    {
        $tenant = Tenant::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->orWhere('domain', $identifier)
            ->first();

        if (! $tenant) {
            $this->error("Tenant [{$identifier}] not found.");

            return Command::FAILURE;
        }

        $this->info("Migrating tenant: {$tenant->name}");

        try {
            TenantFacade::migrate($tenant);

            $this->info("Tenant [{$tenant->name}] migrated successfully.");

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("Migration failed: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    protected function migrateAll(): int
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->warn('No tenants found.');

            return Command::SUCCESS;
        }

        $this->info("Migrating {$tenants->count()} tenants...");
        $this->newLine();

        $bar = $this->output->createProgressBar($tenants->count());
        $bar->start();

        foreach ($tenants as $tenant) {
            try {
                TenantFacade::migrate($tenant);
                $bar->advance();
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("Failed for [{$tenant->name}]: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('All tenants migrated.');

        return Command::SUCCESS;
    }
}
