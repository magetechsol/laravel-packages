<?php

declare(strict_types=1);

namespace MageTech\DevTools\Console;

use Illuminate\Console\Command;
use MageTech\DevTools\DevTools;

class HealthCommand extends Command
{
    protected $signature = 'mts:health';

    protected $description = 'Display application health status';

    public function __construct(
        protected DevTools $devtools,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->devtools->isCommandEnabled()) {
            $this->error('DevTools is disabled. Set MTS_DEVTOOLS_ENABLED=true in .env');

            return Command::FAILURE;
        }

        $this->info('MTS DevTools - Health Status');
        $this->line(str_repeat('─', 50));
        $this->newLine();

        $checks = $this->devtools->getHealthStatus();
        $overall = $this->devtools->getOverallHealth();

        $rows = [];
        foreach ($checks as $check) {
            $rows[] = [
                $check['label'],
                "{$check['status']->icon()} {$check['status']->label()}",
                $check['message'],
            ];
        }

        $this->table(['Check', 'Status', 'Message'], $rows);

        $this->newLine();

        $color = match ($overall->value) {
            'healthy' => 'info',
            'warning' => 'comment',
            'critical' => 'error',
            default => 'comment',
        };

        $this->{$color}("Overall: {$overall->icon()} {$overall->label()}");

        return $overall->value === 'critical' ? Command::FAILURE : Command::SUCCESS;
    }
}
