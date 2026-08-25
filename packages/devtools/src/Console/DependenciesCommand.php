<?php

declare(strict_types=1);

namespace MageTech\DevTools\Console;

use Illuminate\Console\Command;
use MageTech\DevTools\DevTools;

class DependenciesCommand extends Command
{
    protected $signature = 'mts:dependencies
        {--outdated : Show only outdated packages}';

    protected $description = 'Display installed packages and their status';

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

        $this->info('MTS DevTools - Dependencies');
        $this->line(str_repeat('─', 50));
        $this->newLine();

        $data = $this->devtools->getPackageData();

        if (! $this->option('outdated')) {
            $installed = $data['installed'] ?? [];

            $this->info('Installed Packages ('.count($installed).')');
            $this->line(str_repeat('─', 50));

            $rows = [];
            foreach ($installed as $name => $info) {
                $rows[] = [
                    $name,
                    $info['version'] ?? 'N/A',
                    $info['constraint'] ?? '*',
                    $info['dev'] ? 'Yes' : 'No',
                ];
            }

            $this->table(
                ['Package', 'Installed', 'Constraint', 'Dev'],
                $rows
            );

            $this->newLine();
        }

        $outdated = $data['outdated'] ?? [];

        $this->info('Outdated Packages ('.count($outdated).')');
        $this->line(str_repeat('─', 50));

        if (empty($outdated)) {
            $this->info('All packages are up to date.');
        } else {
            $rows = [];
            foreach ($outdated as $name => $info) {
                $rows[] = [
                    $name,
                    $info['current'] ?? 'N/A',
                    $info['latest'] ?? 'N/A',
                ];
            }

            $this->table(
                ['Package', 'Current', 'Latest'],
                $rows
            );
        }

        $this->newLine();

        return Command::SUCCESS;
    }
}
