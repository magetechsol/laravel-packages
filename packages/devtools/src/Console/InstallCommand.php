<?php

declare(strict_types=1);

namespace MageTech\DevTools\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'mts:devtools:install';

    protected $description = 'Install the MTS DevTools package';

    public function handle(): int
    {
        $this->info('Installing MTS DevTools...');
        $this->newLine();

        $this->publishConfig();

        $this->info('MTS DevTools installed successfully!');
        $this->newLine();
        $this->info('Next steps:');
        $this->line('  1. Review config/mts-devtools.php');
        $this->line('  2. Set MTS_DEVTOOLS_ENABLED=true in .env');
        $this->line('  3. Optionally set MTS_DEVTOOLS_PASSWORD in .env');
        $this->line('  4. Visit /devtools in your browser');
        $this->newLine();
        $this->line('Available commands:');
        $this->line('  php artisan mts:doctor          - Full diagnostic check');
        $this->line('  php artisan mts:health          - Health status');
        $this->line('  php artisan mts:performance     - Performance metrics');
        $this->line('  php artisan mts:security        - Security audit');
        $this->line('  php artisan mts:routes          - Route listing');
        $this->line('  php artisan mts:dependencies    - Package status');
        $this->newLine();

        return Command::SUCCESS;
    }

    protected function publishConfig(): void
    {
        $this->callSilent('vendor:publish', [
            '--tag' => 'mts-devtools-config',
            '--force' => true,
        ]);

        $this->info('  Config published to config/mts-devtools.php');
    }
}
