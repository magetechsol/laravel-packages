<?php

declare(strict_types=1);

namespace MageTech\Webhooks;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use MageTech\Webhooks\Console\InstallCommand;
use MageTech\Webhooks\Console\PruneWebhooksCommand;
use MageTech\Webhooks\Console\ReplayWebhookCommand;
use MageTech\Webhooks\Console\RetryWebhooksCommand;
use MageTech\Webhooks\Console\WebhookStatsCommand;
use MageTech\Webhooks\Inbound\Pipeline\Authenticator;
use MageTech\Webhooks\Inbound\Pipeline\DeadLetterHandler;
use MageTech\Webhooks\Inbound\Pipeline\Deduplicator;
use MageTech\Webhooks\Inbound\Pipeline\Persister;
use MageTech\Webhooks\Inbound\Pipeline\Processor;
use MageTech\Webhooks\Inbound\Pipeline\Retrier;
use MageTech\Webhooks\Inbound\Pipeline\Validator;
use MageTech\Webhooks\Outbound\DeliveryTracker;
use MageTech\Webhooks\Outbound\Signer;
use MageTech\Webhooks\Support\SensitiveDataMasker;
use MageTech\Webhooks\Support\RetryStrategy;

class WebhooksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mts-webhooks.php',
            'mts-webhooks',
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
        $helpersFile = __DIR__ . '/Support/helpers.php';

        if (file_exists($helpersFile)) {
            require_once $helpersFile;
        }
    }

    protected function registerServices(): void
    {
        $this->app->scoped(Authenticator::class, function () {
            return new Authenticator();
        });

        $this->app->scoped(Validator::class, function () {
            return new Validator();
        });

        $this->app->scoped(Persister::class, function () {
            return new Persister();
        });

        $this->app->scoped(Deduplicator::class, function () {
            return new Deduplicator();
        });

        $this->app->scoped(Retrier::class, function () {
            return new Retrier();
        });

        $this->app->scoped(DeadLetterHandler::class, function () {
            return new DeadLetterHandler();
        });

        $this->app->scoped(Processor::class, function () {
            return new Processor();
        });

        $this->app->scoped(Signer::class, function () {
            return new Signer();
        });

        $this->app->scoped(DeliveryTracker::class, function () {
            return new DeliveryTracker();
        });

        $this->app->scoped(SensitiveDataMasker::class, function () {
            return new SensitiveDataMasker();
        });

        $this->app->scoped(RetryStrategy::class, function () {
            return new RetryStrategy();
        });
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/mts-webhooks.php' => config_path('mts-webhooks.php'),
        ], 'mts-webhooks-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'mts-webhooks-migrations');

        $this->publishes([
            __DIR__ . '/../routes/webhooks.php' => base_path('routes/webhooks.php'),
        ], 'mts-webhooks-routes');
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
            ReplayWebhookCommand::class,
            RetryWebhooksCommand::class,
            PruneWebhooksCommand::class,
            WebhookStatsCommand::class,
        ]);
    }

    protected function registerRoutes(): void
    {
        if (! config('mts-webhooks.inbound.enabled', true)) {
            return;
        }

        $routeFile = base_path('routes/webhooks.php');

        if (file_exists($routeFile)) {
            Route::prefix(config('mts-webhooks.inbound.route_prefix', 'webhooks'))
                ->middleware(config('mts-webhooks.inbound.route_middleware', []))
                ->group($routeFile);
        } else {
            Route::prefix(config('mts-webhooks.inbound.route_prefix', 'webhooks'))
                ->middleware(config('mts-webhooks.inbound.route_middleware', []))
                ->group(__DIR__ . '/../routes/webhooks.php');
        }
    }
}
