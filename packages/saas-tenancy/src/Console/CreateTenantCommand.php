<?php

declare(strict_types=1);

namespace MageTech\SaaS\Console;

use Illuminate\Console\Command;
use MageTech\SaaS\Support\Facades\Tenant;

class CreateTenantCommand extends Command
{
    protected $signature = 'mts:saas:create-tenant
        {name? : Tenant name}
        {--slug= : Tenant slug (auto-generated if not provided)}
        {--domain= : Custom domain for the tenant}
        {--database= : Custom database name (database strategy only)}';

    protected $description = 'Create a new tenant';

    public function handle(): int
    {
        $name = $this->argument('name') ?? $this->ask('Tenant name');

        if (! $name) {
            $this->error('Tenant name is required.');

            return Command::FAILURE;
        }

        $slug = $this->option('slug') ?? \Str::slug($name);
        $domain = $this->option('domain') ?? $slug;
        $database = $this->option('database');

        $this->info("Creating tenant: {$name}");
        $this->newLine();

        try {
            $data = [
                'name' => $name,
                'slug' => $slug,
                'domain' => $domain,
                'status' => 'active',
                'activated_at' => now(),
            ];

            if ($database) {
                $data['database'] = $database;
            }

            $tenant = Tenant::create($data);

            $this->info("Tenant created successfully!");
            $this->newLine();
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $tenant->getKey()],
                    ['Name', $tenant->name],
                    ['Slug', $tenant->slug],
                    ['Domain', $tenant->domain],
                    ['Status', $tenant->status],
                ]
            );

            if ($this->confirm('Run tenant migrations?', true)) {
                Tenant::migrate($tenant);
                $this->info('Migrations completed.');
            }

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("Failed to create tenant: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
