<?php

declare(strict_types=1);

namespace MageTech\DevTools;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use MageTech\DevTools\Collectors\ApplicationCollector;
use MageTech\DevTools\Collectors\PackageCollector;
use MageTech\DevTools\Collectors\PerformanceCollector;
use MageTech\DevTools\Collectors\SecurityCollector;
use MageTech\DevTools\Console\DependenciesCommand;
use MageTech\DevTools\Console\DoctorCommand;
use MageTech\DevTools\Console\HealthCommand;
use MageTech\DevTools\Console\InstallCommand;
use MageTech\DevTools\Console\PerformanceCommand;
use MageTech\DevTools\Console\RoutesCommand;
use MageTech\DevTools\Console\SecurityCommand;
use MageTech\DevTools\Http\Middleware\DevToolsAccessMiddleware;

class DevToolsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/mts-devtools.php',
            'mts-devtools'
        );

        $this->registerHelpers();
        $this->registerServices();
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerCommands();
        $this->registerRoutes();
    }

    protected function registerHelpers(): void
    {
        $helpers = __DIR__.'/Support/helpers.php';

        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }

    protected function registerServices(): void
    {
        $this->app->scoped(ApplicationCollector::class, function ($app) {
            return new ApplicationCollector(
                $app['config'],
                $app['db']
            );
        });

        $this->app->scoped(PerformanceCollector::class, function ($app) {
            return new PerformanceCollector($app['config']);
        });

        $this->app->scoped(SecurityCollector::class, function ($app) {
            return new SecurityCollector($app['config']);
        });

        $this->app->scoped(PackageCollector::class, function ($app) {
            return new PackageCollector(
                $app['config'],
                $app['composer']
            );
        });

        $this->app->scoped(DevTools::class, function ($app) {
            return new DevTools(
                $app['config'],
                $app->make(ApplicationCollector::class),
                $app->make(PerformanceCollector::class),
                $app->make(SecurityCollector::class),
                $app->make(PackageCollector::class)
            );
        });
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/mts-devtools.php' => config_path('mts-devtools.php'),
        ], 'mts-devtools-config');
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        if (! config('mts-devtools.commands', true)) {
            return;
        }

        $this->commands([
            InstallCommand::class,
            DoctorCommand::class,
            HealthCommand::class,
            PerformanceCommand::class,
            SecurityCommand::class,
            RoutesCommand::class,
            DependenciesCommand::class,
        ]);
    }

    protected function registerRoutes(): void
    {
        if (! config('mts-devtools.dashboard', true)) {
            return;
        }

        if (! config('mts-devtools.enabled', false)) {
            return;
        }

        Route::group([
            'prefix' => config('mts-devtools.prefix', 'devtools'),
            'as' => 'devtools.',
            'middleware' => ['web', DevToolsAccessMiddleware::class],
        ], function (): void {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }
}
