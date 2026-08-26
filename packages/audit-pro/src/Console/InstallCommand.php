<?php

declare(strict_types=1);

namespace MageTech\Audit\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'audit:install';

    protected $description = 'Install the MTS Laravel Audit Pro package';

    public function handle(): int
    {
        $this->info('MTS Laravel Audit Pro - Installation');
        $this->newLine();

        // Publish config
        $this->call('vendor:publish', [
            '--tag' => 'audit-config',
            '--force' => true,
        ]);

        $this->info('Configuration published successfully.');

        // Publish migrations
        $this->call('vendor:publish', [
            '--tag' => 'audit-migrations',
            '--force' => true,
        ]);

        $this->info('Migrations published successfully.');

        // Run migrations
        if ($this->confirm('Would you like to run migrations now?', true)) {
            $this->call('migrate');
            $this->info('Migrations completed successfully.');
        }

        $this->newLine();
        $this->info('Installation complete!');
        $this->newLine();
        $this->line('Next steps:');
        $this->line('  1. Review config/audit.php');
        $this->line('  2. Add the Auditable trait to your models');
        $this->line('  3. Use Audit::record() for manual audit logging');
        $this->newLine();
        $this->line('Documentation: https://github.com/magetechsol/laravel-audit');
        $this->line('Developed by MageTech Solutions - https://www.magetechsol.com/');

        return self::SUCCESS;
    }
}
