<?php

declare(strict_types=1);

namespace MageTech\Workflow\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'mts:workflow:install';

    protected $description = 'Publish workflow config, migrations, and set up the package';

    public function handle(): int
    {
        $this->publishes([
            __DIR__ . '/../../config/mts-workflow.php' => config_path('mts-workflow.php'),
        ], 'mts-workflow-config');

        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'mts-workflow-migrations');

        $this->info('Workflow Engine installed successfully.');
        $this->line('');
        $this->line('Published:');
        $this->line('  - Config: config/mts-workflow.php (tag: mts-workflow-config)');
        $this->line('  - Migrations: database/migrations/ (tag: mts-workflow-migrations)');
        $this->line('');
        $this->line('Next steps:');
        $this->line('  1. Run: php artisan migrate');
        $this->line('  2. Create a workflow: php artisan mts:workflow:make OrderWorkflow');

        return self::SUCCESS;
    }
}
