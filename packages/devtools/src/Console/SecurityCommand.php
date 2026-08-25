<?php

declare(strict_types=1);

namespace MageTech\DevTools\Console;

use Illuminate\Console\Command;
use MageTech\DevTools\DevTools;

class SecurityCommand extends Command
{
    protected $signature = 'mts:security';

    protected $description = 'Display security configuration audit';

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

        $this->info('MTS DevTools - Security');
        $this->line(str_repeat('─', 50));
        $this->newLine();

        $data = $this->devtools->getSecurityData();

        $this->info('Configuration');
        $this->table(
            ['Check', 'Status', 'Risk'],
            [
                ['Debug Mode', $data['debug_mode']['status'] ?? 'N/A', $data['debug_mode']['risk'] ?? 'N/A'],
                ['Environment', $data['environment']['status'] ?? 'N/A', $data['environment']['is_production'] ? 'low' : 'info'],
                ['Config Cached', $data['configuration']['status'] ?? 'N/A', 'info'],
                ['HTTPS', ($data['https'] ?? false) ? 'Yes' : 'No', ($data['https'] ?? false) ? 'low' : 'medium'],
            ]
        );

        $this->newLine();

        $this->info('Routes');
        $routeData = $data['routes'] ?? [];
        $this->line('Total routes: '.($routeData['total'] ?? 0));

        if (! empty($routeData['methods'])) {
            $this->newLine();
            $this->table(
                ['Method', 'Count'],
                collect($routeData['methods'])->map(fn ($count, $method) => [$method, $count])->values()->all()
            );
        }

        $this->newLine();

        $this->info('PHP Extensions');
        $extensions = $data['php_extensions'] ?? [];
        $rows = [];
        foreach ($extensions as $name => $info) {
            $rows[] = [
                $name,
                $info['loaded'] ? '✓ Loaded' : '✗ Missing',
                $info['description'],
            ];
        }
        $this->table(['Extension', 'Status', 'Description'], $rows);

        $this->newLine();

        return Command::SUCCESS;
    }
}
