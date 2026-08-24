<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'mts:import-export:install', description: 'Install the MTS Import Export package')]
class InstallCommand extends Command
{
    protected $signature = 'mts:import-export:install';

    protected $description = 'Install the MTS Import Export package';

    public function handle(): int
    {
        $this->info('Installing MTS Import Export...');
        $this->newLine();

        $this->publishConfig();
        $this->publishMigrations();
        $this->createDirectories();

        $this->info('MTS Import Export installed successfully!');
        $this->newLine();
        $this->info('Next steps:');
        $this->line('  1. Review config/mts-import-export.php');
        $this->line('  2. Run: php artisan migrate');
        $this->line('  3. Configure your queue connection in .env');
        $this->line('  4. Use Import::make() and Export::make() facades');
        $this->newLine();

        return Command::SUCCESS;
    }

    protected function publishConfig(): void
    {
        $source = __DIR__.'/../../config/mts-import-export.php';
        $destination = config_path('mts-import-export.php');

        if (File::exists($destination)) {
            $this->warn('Config file already exists. Skipping...');
        } else {
            File::copy($source, $destination);
            $this->info('Config published to config/mts-import-export.php');
        }
    }

    protected function publishMigrations(): void
    {
        $source = __DIR__.'/../Database/migrations';
        $destination = database_path('migrations');

        if (! is_dir($source)) {
            return;
        }

        $files = glob($source.'/*.php');

        foreach ($files as $file) {
            $destFile = $destination.'/'.basename($file);

            if (! File::exists($destFile)) {
                File::copy($file, $destFile);
                $this->info('Migration published: '.basename($file));
            }
        }
    }

    protected function createDirectories(): void
    {
        $directories = [
            storage_path('app/imports'),
            storage_path('app/exports'),
            storage_path('app/error_reports'),
        ];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
                $this->info('Created directory: '.$directory);
            }
        }
    }
}
