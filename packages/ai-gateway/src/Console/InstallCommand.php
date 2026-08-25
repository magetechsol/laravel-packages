<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'mts:ai-gateway:install';

    protected $description = 'Install the MTS AI Gateway package';

    public function handle(): int
    {
        $this->info('Installing MTS AI Gateway...');
        $this->newLine();

        $this->publishConfig();
        $this->publishMigrations();
        $this->runMigrations();

        $this->info('MTS AI Gateway installed successfully!');
        $this->newLine();
        $this->info('Next steps:');
        $this->line('  1. Review config/mts-ai.php');
        $this->line('  2. Add your AI provider API keys to .env');
        $this->line('  3. Run: php artisan migrate');
        $this->newLine();
        $this->line('Providers configured in .env:');
        $this->line('  MTS_AI_DEFAULT_PROVIDER=openai');
        $this->line('  OPENAI_API_KEY=sk-...');
        $this->line('  ANTHROPIC_API_KEY=sk-ant-...');
        $this->line('  GEMINI_API_KEY=...');
        $this->newLine();

        return Command::SUCCESS;
    }

    protected function publishConfig(): void
    {
        $this->callSilent('vendor:publish', [
            '--tag' => 'mts-ai-config',
            '--force' => true,
        ]);

        $this->info('  Config published to config/mts-ai.php');
    }

    protected function publishMigrations(): void
    {
        $this->callSilent('vendor:publish', [
            '--tag' => 'mts-ai-migrations',
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
