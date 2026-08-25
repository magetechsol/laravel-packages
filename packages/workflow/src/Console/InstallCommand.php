<?php

declare(strict_types=1);

namespace MageTech\Workflow\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature = 'mts:workflow:install';

    protected $description = 'Publish workflow config, migrations, and set up the package';

    public function handle(): int
    {
        Artisan::call('vendor:publish', [
            '--provider' => \MageTech\Workflow\WorkflowServiceProvider::class,
            '--tag' => 'mts-workflow-config',
        ], $this->output);

        Artisan::call('vendor:publish', [
            '--provider' => \MageTech\Workflow\WorkflowServiceProvider::class,
            '--tag' => 'mts-workflow-migrations',
        ], $this->output);

        $this->info('');
        $this->info('Workflow Engine installed successfully.');
        $this->line('');
        $this->line('Next steps:');
        $this->line('  1. Run: php artisan migrate');
        $this->line('  2. Create a workflow: php artisan mts:workflow:make OrderWorkflow');

        return self::SUCCESS;
    }
}
