<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags;

use Illuminate\Support\ServiceProvider;
use MageTech\FeatureFlags\Contracts\FeatureCacheContract;
use MageTech\FeatureFlags\Contracts\FeatureEvaluatorContract;
use MageTech\FeatureFlags\Contracts\FeatureRepositoryContract;
use MageTech\FeatureFlags\Services\FeatureCache;
use MageTech\FeatureFlags\Services\FeatureEvaluator;
use MageTech\FeatureFlags\Services\FeatureFlagService;
use MageTech\FeatureFlags\Services\FeatureRepository;
use MageTech\FeatureFlags\Support\BladeCompiler;
use MageTech\FeatureFlags\Support\EnvironmentResolver;
use MageTech\FeatureFlags\Support\PercentageRollout;
use MageTech\FeatureFlags\Support\RuleEngine;

class FeatureFlagsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mts-feature-flags.php',
            'mts-feature-flags'
        );

        $this->app->scoped(EnvironmentResolver::class, function () {
            return new EnvironmentResolver();
        });

        $this->app->scoped(PercentageRollout::class, function () {
            return new PercentageRollout();
        });

        $this->app->scoped(RuleEngine::class, function ($app) {
            return new RuleEngine($app);
        });

        $this->app->scoped(FeatureCacheContract::class, function () {
            return new FeatureCache();
        });

        $this->app->scoped(FeatureRepositoryContract::class, function ($app) {
            return new FeatureRepository(
                $app->make(FeatureCacheContract::class),
            );
        });

        $this->app->scoped(FeatureEvaluatorContract::class, function ($app) {
            return new FeatureEvaluator(
                $app->make(RuleEngine::class),
                $app->make(PercentageRollout::class),
                $app->make(EnvironmentResolver::class),
            );
        });

        $this->app->scoped(FeatureFlagService::class, function ($app) {
            return new FeatureFlagService(
                $app->make(FeatureRepositoryContract::class),
                $app->make(FeatureEvaluatorContract::class),
                $app->make(FeatureCacheContract::class),
            );
        });
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerCommands();
        $this->registerRoutes();
        $this->registerMiddleware();
        $this->registerBladeDirectives();
        $this->registerPolicies();
        $this->loadHelpers();
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/mts-feature-flags.php' => config_path('mts-feature-flags.php'),
        ], 'mts-feature-flags-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'mts-feature-flags-migrations');
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Console\InstallCommand::class,
            Console\ListCommand::class,
            Console\CreateCommand::class,
            Console\EnableCommand::class,
            Console\DisableCommand::class,
            Console\ClearCacheCommand::class,
            Console\CheckCommand::class,
            Console\ExportCommand::class,
            Console\ImportCommand::class,
        ]);
    }

    protected function registerRoutes(): void
    {
        if (! config('mts-feature-flags.api.enabled', false)) {
            return;
        }

        $this->app->booted(function () {
            $prefix = config('mts-feature-flags.api.prefix', 'api/feature-flags');
            $middleware = config('mts-feature-flags.api.middleware', ['api', 'auth:sanctum']);

            $this->app['router']
                ->prefix($prefix)
                ->middleware($middleware)
                ->group(__DIR__ . '/routes/api.php');
        });
    }

    protected function registerMiddleware(): void
    {
        $this->app['router']->aliasMiddleware(
            'feature',
            \MageTech\FeatureFlags\Http\Middleware\FeatureFlagMiddleware::class
        );
    }

    protected function registerBladeDirectives(): void
    {
        $compiler = new BladeCompiler();
        $compiler->register();
    }

    protected function registerPolicies(): void
    {
        $this->app->booted(function () {
            if (method_exists($this->app['gate'], 'policy')) {
                $this->app['gate']->policy(
                    \MageTech\FeatureFlags\Models\FeatureFlag::class,
                    \MageTech\FeatureFlags\Policies\FeatureFlagPolicy::class
                );
            }
        });
    }

    protected function loadHelpers(): void
    {
        if (! config('mts-feature-flags.helpers.enabled', true)) {
            return;
        }

        $helpers = __DIR__ . '/Support/helpers.php';

        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }
}
