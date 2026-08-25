<?php

declare(strict_types=1);

namespace MageTech\DevTools\Console;

use Illuminate\Console\Command;
use MageTech\DevTools\DevTools;

class PerformanceCommand extends Command
{
    protected $signature = 'mts:performance
        {--slow-only : Show only slow queries}';

    protected $description = 'Display application performance metrics';

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

        $this->info('MTS DevTools - Performance');
        $this->line(str_repeat('─', 50));
        $this->newLine();

        $data = $this->devtools->getPerformanceData();

        $this->info('Metrics');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Requests', number_format($data['requests'] ?? 0)],
                ['Queries', number_format($data['queries'] ?? 0)],
                ['Slow Queries', number_format(count($data['slow_queries'] ?? []))],
                ['Queued Jobs', number_format($data['jobs'] ?? 0)],
                ['Failed Jobs', number_format($data['failed_jobs'] ?? 0)],
                ['Cache Hits', number_format($data['cache']['hit'] ?? 0)],
                ['Cache Misses', number_format($data['cache']['miss'] ?? 0)],
                ['Cache Driver', $data['cache']['driver'] ?? 'N/A'],
            ]
        );

        $slowQueries = $data['slow_queries'] ?? [];

        if ($this->option('slow-only') || count($slowQueries) > 0) {
            $this->newLine();
            $this->info('Slow Queries (last 10)');
            $this->line(str_repeat('─', 50));

            $recent = array_slice($slowQueries, -10);

            if (empty($recent)) {
                $this->info('No slow queries detected.');
            } else {
                $rows = [];
                foreach ($recent as $query) {
                    $rows[] = [
                        number_format($query['duration_ms'], 1).'ms',
                        Str::limit($query['query'], 80),
                    ];
                }
                $this->table(['Duration', 'Query'], $rows);
            }
        }

        $this->newLine();

        return Command::SUCCESS;
    }
}
