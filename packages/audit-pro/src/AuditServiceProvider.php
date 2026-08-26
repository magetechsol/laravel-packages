<?php

declare(strict_types=1);

namespace MageTech\Audit;

use Illuminate\Support\ServiceProvider;
use MageTech\Audit\Contracts\ActorResolver;
use MageTech\Audit\Contracts\AuditIntegrityService;
use MageTech\Audit\Contracts\AuditStore;
use MageTech\Audit\Contracts\TenantResolver;
use MageTech\Audit\Services\Auditor;
use MageTech\Audit\Services\AuthenticatedUserResolver;
use MageTech\Audit\Services\AuditIntegrityService as AuditIntegrityServiceImplementation;
use MageTech\Audit\Services\DefaultTenantResolver;
use MageTech\Audit\Stores\DatabaseAuditStore;

class AuditServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPublishes();
        $this->registerMigrations();
        $this->registerRoutes();
        $this->registerCommands();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/audit.php', 'audit');

        $this->registerBindings();
        $this->registerAuditor();
    }

    protected function registerPublishes(): void
    {
        $this->publishes([
            __DIR__ . '/../config/audit.php' => config_path('audit.php'),
        ], ['audit-config', 'audit-config']);

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], ['audit-migrations', 'audit-migrations']);
    }

    protected function registerMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    protected function registerRoutes(): void
    {
        if (config('audit.api.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
                Console\VerifyIntegrityCommand::class,
                Console\ExportCommand::class,
                Console\CleanupCommand::class,
                Console\StatsCommand::class,
                Console\PruneCommand::class,
            ]);
        }
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(AuditStore::class, function () {
            return match (config('audit.driver', 'database')) {
                'database' => new DatabaseAuditStore(),
                default => new DatabaseAuditStore(),
            };
        });

        $this->app->singleton(ActorResolver::class, function () {
            $resolverClass = config('audit.actor.resolver', AuthenticatedUserResolver::class);

            return new $resolverClass();
        });

        $this->app->singleton(TenantResolver::class, function () {
            $resolverClass = config('audit.tenancy.resolver', DefaultTenantResolver::class);

            return new $resolverClass();
        });

        if (config('audit.integrity.enabled', false)) {
            $this->app->singleton(AuditIntegrityService::class, function () {
                return new AuditIntegrityServiceImplementation();
            });
        }
    }

    protected function registerAuditor(): void
    {
        $this->app->singleton(Auditor::class, function ($app) {
            return new Auditor(
                store: $app->make(AuditStore::class),
                integrityService: $app->bound(AuditIntegrityService::class)
                    ? $app->make(AuditIntegrityService::class)
                    : null,
            );
        });

        $this->app->bind('audit', function ($app) {
            return $app->make(Auditor::class);
        });
    }
}
