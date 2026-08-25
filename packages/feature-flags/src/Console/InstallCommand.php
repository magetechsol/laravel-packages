<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature = 'mts:feature-flags:install';

    protected $description = 'Publish feature flags config, migrations, and set up the package';

    public function handle(): int
    {
        Artisan::call('vendor:publish', [
            '--provider' => \MageTech\FeatureFlags\FeatureFlagsServiceProvider::class,
            '--tag' => 'mts-feature-flags-config',
        ], $this->output);

        Artisan::call('vendor:publish', [
            '--provider' => \MageTech\FeatureFlags\FeatureFlagsServiceProvider::class,
            '--tag' => 'mts-feature-flags-migrations',
        ], $this->output);

        $this->info('');
        $this->info('Feature Flags package installed successfully.');
        $this->line('');
        $this->line('Next steps:');
        $this->line('  1. Run: php artisan migrate');
        $this->line('  2. Create flags: php artisan mts:feature-flags:create');

        return self::SUCCESS;
    }
}
