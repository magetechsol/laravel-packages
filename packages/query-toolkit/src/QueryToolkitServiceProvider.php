<?php

declare(strict_types=1);

namespace MageTech\QueryToolkit;

use Illuminate\Support\ServiceProvider;
use MageTech\QueryToolkit\Console\InstallCommand;
use MageTech\QueryToolkit\Console\MakeFilterCommand;
use MageTech\QueryToolkit\Console\MakeSearchCommand;
use MageTech\QueryToolkit\Console\MakeSortCommand;

class QueryToolkitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mts-query.php',
            'mts-query'
        );

        $this->app->scoped('mts-query', function () {
            return new class {
                public function for(string $modelClass, $request = null, array $options = []): QueryBuilder
                {
                    return QueryBuilder::for($modelClass, $request, $options);
                }

                public function fromQuery($query, $request = null, array $options = []): QueryBuilder
                {
                    return QueryBuilder::fromQuery($query, $request, $options);
                }
            };
        });
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerCommands();
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/mts-query.php' => config_path('mts-query.php'),
        ], 'mts-query-config');
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
            MakeFilterCommand::class,
            MakeSortCommand::class,
            MakeSearchCommand::class,
        ]);
    }
}