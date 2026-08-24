<?php

declare(strict_types=1);

namespace MageTech\ImportExport;

use Illuminate\Support\ServiceProvider;
use MageTech\ImportExport\Console\CancelImportCommand;
use MageTech\ImportExport\Console\ExportCommand;
use MageTech\ImportExport\Console\InstallCommand;
use MageTech\ImportExport\Console\MakeImportCommand;
use MageTech\ImportExport\Console\ProcessImportCommand;
use MageTech\ImportExport\Console\RetryImportCommand;

class ImportExportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/mts-import-export.php',
            'mts-import-export',
        );

        $this->app->scoped(ProgressTracker::class, function () {
            return new ProgressTracker;
        });

        $this->app->scoped(ErrorReport::class, function () {
            return new ErrorReport;
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
            __DIR__.'/../config/mts-import-export.php' => config_path('mts-import-export.php'),
        ], 'mts-import-export-config');

        $this->publishes([
            __DIR__.'/Database/migrations' => database_path('migrations'),
        ], 'mts-import-export-migrations');
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
            ProcessImportCommand::class,
            RetryImportCommand::class,
            CancelImportCommand::class,
            ExportCommand::class,
            MakeImportCommand::class,
        ]);
    }
}
