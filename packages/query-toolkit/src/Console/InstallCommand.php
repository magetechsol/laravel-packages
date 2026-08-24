<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'mts:query:install';

    protected $description = 'Install the MTS Query Toolkit configuration file';

    public function handle(): int
    {
        $configPath = config_path('mts-query.php');

        if (file_exists($configPath)) {
            $this->warn('Configuration file already exists at: ' . $configPath);

            if (! $this->confirm('Do you want to overwrite it?')) {
                $this->info('Installation cancelled.');

                return self::SUCCESS;
            }
        }

        $this->call('vendor:publish', [
            '--provider' => 'MageTech\\QueryToolkit\\QueryToolkitServiceProvider',
            '--tag' => 'mts-query-config',
            '--force' => true,
        ]);

        $this->newLine();
        $this->info('MTS Query Toolkit installed successfully!');
        $this->newLine();
        $this->info('Configuration file published to: ' . $configPath);
        $this->newLine();
        $this->comment('You can now use the QueryBuilder in your controllers:');
        $this->newLine();
        $this->line('  use MageTech\\QueryToolkit\\Support\\Facades\\MtsQuery;');
        $this->newLine();
        $this->line('  $users = MtsQuery::for(User::class)');
        $this->line('      ->allowedFilters([\'name\', \'email\', \'status\'])');
        $this->line('      ->allowedSorts([\'name\', \'created_at\'])');
        $this->line('      ->allowedIncludes([\'posts\', \'roles\'])');
        $this->line('      ->searchable([\'name\', \'email\'])');
        $this->line('      ->paginate();');
        $this->newLine();

        return self::SUCCESS;
    }
}