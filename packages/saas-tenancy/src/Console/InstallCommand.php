<?php

declare(strict_types=1);

namespace MageTech\SaaS\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'mts:saas:install';

    protected $description = 'Install the MTS SaaS Tenancy package';

    public function handle(): int
    {
        $this->info('Installing MTS SaaS Tenancy...');
        $this->newLine();

        $this->publishConfig();
        $this->publishMigrations();
        $this->runMigrations();

        $this->info('MTS SaaS Tenancy installed successfully!');
        $this->newLine();
        $this->info('Next steps:');
        $this->line('  1. Review config/mts-saas.php');
        $this->line('  2. Set MTS_SAAS_STRATEGY in .env (shared or database)');
        $this->line('  3. Run: php artisan migrate');
        $this->line('  4. Run: php artisan mts:saas:create-tenant');
        $this->newLine();

        return Command::SUCCESS;
    }

    protected function publishConfig(): void
    {
        $this->callSilent('vendor:publish', [
            '--tag' => 'mts-saas-config',
            '--force' => true,
        ]);

        $this->info('  Config published to config/mts-saas.php');
    }

    protected function publishMigrations(): void
    {
        $this->callSilent('vendor:publish', [
            '--tag' => 'mts-saas-migrations',
        ]);

        $this->info('  Migrations published');
    }

    protected function runMigrations(): void
    {
        if ($this->confirm('Run migrations now?', true)) {
            $this->call('migrate');
        }
    }
}
