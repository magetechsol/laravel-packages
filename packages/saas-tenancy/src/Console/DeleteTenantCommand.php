<?php

declare(strict_types=1);

namespace MageTech\SaaS\Console;

use Illuminate\Console\Command;
use MageTech\SaaS\Models\Tenant;
use MageTech\SaaS\Support\Facades\Tenant as TenantFacade;

class DeleteTenantCommand extends Command
{
    protected $signature = 'mts:saas:delete-tenant
        {identifier : Tenant ID, slug, or domain}
        {--force : Skip confirmation}';

    protected $description = 'Delete a tenant and all its data';

    public function handle(): int
    {
        $identifier = $this->argument('identifier');

        $tenant = $this->findTenant($identifier);

        if (! $tenant) {
            $this->error("Tenant [{$identifier}] not found.");

            return Command::FAILURE;
        }

        $this->warn("This will permanently delete tenant: {$tenant->name}");
        $this->warn("All data associated with this tenant will be lost.");

        if (! $this->option('force') && ! $this->confirm('Are you sure you want to delete this tenant?')) {
            $this->info('Cancelled.');

            return Command::SUCCESS;
        }

        try {
            TenantFacade::delete($tenant);

            $this->info("Tenant [{$tenant->name}] deleted successfully.");

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("Failed to delete tenant: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    protected function findTenant(string $identifier): ?Tenant
    {
        return Tenant::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->orWhere('domain', $identifier)
            ->first();
    }
}
