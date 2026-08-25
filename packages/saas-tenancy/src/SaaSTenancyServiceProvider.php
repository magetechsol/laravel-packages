<?php

declare(strict_types=1);

namespace MageTech\SaaS;

use Illuminate\Support\ServiceProvider;
use MageTech\SaaS\Console\CreateTenantCommand;
use MageTech\SaaS\Console\DeleteTenantCommand;
use MageTech\SaaS\Console\InstallCommand;
use MageTech\SaaS\Console\MigrateTenantCommand;
use MageTech\SaaS\Contracts\DatabaseStrategyContract;
use MageTech\SaaS\Contracts\TenantManagerContract;
use MageTech\SaaS\Contracts\TenantResolverContract;
use MageTech\SaaS\Database\DatabasePerTenantStrategy;
use MageTech\SaaS\Database\SharedDatabaseStrategy;
use MageTech\SaaS\Database\DatabaseStrategyFactory;
use MageTech\SaaS\Support\Facades\Tenant;

class SaaSTenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mts-saas.php',
            'mts-saas'
        );

        $this->registerHelpers();
        $this->registerServices();
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerCommands();
    }

    protected function registerHelpers(): void
    {
        $helpers = __DIR__ . '/Support/helpers.php';

        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }

    protected function registerServices(): void
    {
        $this->app->scoped(TenantManager::class, function ($app) {
            return new TenantManager(
                config: $app['config'],
                resolver: $app->make(TenantResolverContract::class),
                strategy: $app->make(DatabaseStrategyContract::class),
            );
        });

        $this->app->scoped(TenantManagerContract::class, function ($app) {
            return $app->make(TenantManager::class);
        });

        $this->app->scoped(TenantResolverContract::class, function ($app) {
            return new TenantResolver(
                config: $app['config'],
                request: $app['request'],
            );
        });

        $this->app->scoped(DatabaseStrategyFactory::class, function ($app) {
            return new DatabaseStrategyFactory($app['config']);
        });

        $this->app->scoped(DatabaseStrategyContract::class, function ($app) {
            return $app->make(DatabaseStrategyFactory::class)->create();
        });

        $this->app->scoped('tenant', function ($app) {
            return $app->make(TenantManager::class);
        });
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/mts-saas.php' => config_path('mts-saas.php'),
        ], 'mts-saas-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'mts-saas-migrations');
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
            CreateTenantCommand::class,
            DeleteTenantCommand::class,
            MigrateTenantCommand::class,
        ]);
    }
}
