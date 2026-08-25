<?php

declare(strict_types=1);

namespace MageTech\DevTools\Console;

use Illuminate\Console\Command;
use MageTech\DevTools\DevTools;

class DoctorCommand extends Command
{
    protected $signature = 'mts:doctor';

    protected $description = 'Run full diagnostic check on your application';

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

        $this->info('MTS DevTools - Doctor');
        $this->line(str_repeat('─', 50));
        $this->newLine();

        $this->checkApplication();
        $this->checkPerformance();
        $this->checkSecurity();
        $this->checkHealth();

        $overall = $this->devtools->getOverallHealth();
        $this->newLine();
        $this->line(str_repeat('─', 50));
        $this->newLine();

        $color = match ($overall->value) {
            'healthy' => 'info',
            'warning' => 'comment',
            'critical' => 'error',
            default => 'comment',
        };

        $this->{$color}("Overall Status: {$overall->icon()} {$overall->label()}");

        return $overall->value === 'critical' ? Command::FAILURE : Command::SUCCESS;
    }

    protected function checkApplication(): void
    {
        $this->info('Application');
        $this->line(str_repeat('─', 50));

        $data = $this->devtools->getApplicationData();

        $this->table(
            ['Property', 'Value'],
            [
                ['Laravel', $data['laravel'] ?? 'N/A'],
                ['PHP', $data['php'] ?? 'N/A'],
                ['Environment', $data['environment'] ?? 'N/A'],
                ['Database', ($data['database']['driver'] ?? 'N/A').' ('.($data['database']['version'] ?? 'N/A').')'],
                ['Cache', $data['cache']['default'] ?? 'N/A'],
                ['Queue', $data['queue']['default'] ?? 'N/A'],
            ]
        );

        $this->newLine();
    }

    protected function checkPerformance(): void
    {
        $this->info('Performance');
        $this->line(str_repeat('─', 50));

        $data = $this->devtools->getPerformanceData();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Requests', number_format($data['requests'] ?? 0)],
                ['Queries', number_format($data['queries'] ?? 0)],
                ['Slow Queries', number_format(count($data['slow_queries'] ?? []))],
                ['Jobs', number_format($data['jobs'] ?? 0)],
                ['Failed Jobs', number_format($data['failed_jobs'] ?? 0)],
                ['Cache Driver', $data['cache']['driver'] ?? 'N/A'],
            ]
        );

        $this->newLine();
    }

    protected function checkSecurity(): void
    {
        $this->info('Security');
        $this->line(str_repeat('─', 50));

        $data = $this->devtools->getSecurityData();

        $this->table(
            ['Check', 'Status'],
            [
                ['Debug Mode', ($data['debug_mode']['status'] ?? 'N/A').' ('.($data['debug_mode']['risk'] ?? 'N/A').' risk)'],
                ['Environment', $data['environment']['status'] ?? 'N/A'],
                ['Config Cached', $data['configuration']['status'] ?? 'N/A'],
                ['Routes', ($data['routes']['total'] ?? 0).' total'],
                ['HTTPS', ($data['https'] ?? false) ? 'Yes' : 'No'],
            ]
        );

        $this->newLine();
    }

    protected function checkHealth(): void
    {
        $this->info('Health Checks');
        $this->line(str_repeat('─', 50));

        $checks = $this->devtools->getHealthStatus();

        foreach ($checks as $check) {
            $status = "{$check['status']->icon()} {$check['label']}: {$check['message']}";
            $method = $check['status']->value === 'critical' ? 'error' : (
                $check['status']->value === 'warning' ? 'comment' : 'info'
            );
            $this->{$method}($status);
        }

        $this->newLine();
    }
}
