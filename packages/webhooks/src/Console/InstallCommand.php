<?php

declare(strict_types=1);

namespace MageTech\Webhooks\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'mts:webhooks:install';

    protected $description = 'Publish configuration, migrations, and route files for MTS Laravel Webhooks';

    public function handle(): int
    {
        $this->publishConfig();
        $this->publishMigrations();
        $this->publishRoutes();

        $this->info('');
        $this->info('MTS Laravel Webhooks installed successfully.');
        $this->info('');
        $this->line('Next steps:');
        $this->line('  1. Run <info>php artisan migrate</info> to create the webhook tables');
        $this->line('  2. Configure webhook provider secrets in <info>config/mts-webhooks.php</info>');
        $this->line('  3. Register webhook handlers in the <info>processing.handler_map</info> config');
        $this->line('');

        return self::SUCCESS;
    }

    protected function publishConfig(): void
    {
        $source = __DIR__ . '/../../config/mts-webhooks.php';
        $destination = config_path('mts-webhooks.php');

        if (File::exists($destination) && ! $this->option('force')) {
            $this->line('Config file already exists. Skipping. Use --force to overwrite.');
            return;
        }

        $this->publishes([
            $source => $destination,
        ], 'mts-webhooks-config');

        $this->line('Config published to <info>' . $destination . '</info>');
    }

    protected function publishMigrations(): void
    {
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'mts-webhooks-migrations');

        $this->line('Migrations published.');
    }

    protected function publishRoutes(): void
    {
        $source = __DIR__ . '/../../routes/webhooks.php';
        $destination = base_path('routes/webhooks.php');

        if (! File::exists($destination)) {
            File::copy($source, $destination);
            $this->line('Routes published to <info>' . $destination . '</info>');
        } else {
            $this->line('Routes file already exists. Skipping.');
        }
    }
}
