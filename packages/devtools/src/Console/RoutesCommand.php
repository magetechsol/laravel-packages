<?php

declare(strict_types=1);

namespace MageTech\DevTools\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use MageTech\DevTools\DevTools;

class RoutesCommand extends Command
{
    protected $signature = 'mts:routes
        {--method= : Filter by HTTP method}
        {--name= : Filter by route name}';

    protected $description = 'Display all registered routes with details';

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

        $this->info('MTS DevTools - Routes');
        $this->line(str_repeat('─', 50));
        $this->newLine();

        $routes = Route::getRoutes();
        $method = $this->option('method');
        $name = $this->option('name');

        $rows = [];

        foreach ($routes as $route) {
            $routeMethods = implode(', ', $route->methods());

            if ($method && ! in_array(strtoupper($method), $route->methods())) {
                continue;
            }

            if ($name && ! str_contains($route->getName() ?? '', $name)) {
                continue;
            }

            $middleware = implode(', ', $route->gatherMiddleware());

            $rows[] = [
                $routeMethods,
                $route->getUri(),
                $route->getName() ?? '-',
                $route->getActionName() ?: '-',
                Str::limit($middleware, 50),
            ];
        }

        if (empty($rows)) {
            $this->warn('No routes found matching the criteria.');
        } else {
            $this->table(
                ['Method', 'URI', 'Name', 'Action', 'Middleware'],
                $rows
            );
        }

        $this->newLine();
        $this->info('Total routes: '.count($rows));

        return Command::SUCCESS;
    }
}
