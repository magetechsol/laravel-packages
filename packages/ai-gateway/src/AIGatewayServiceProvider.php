<?php

declare(strict_types=1);

namespace MageTech\AIGateway;

use Illuminate\Support\ServiceProvider;
use MageTech\AIGateway\Cost\CostEstimator;
use MageTech\AIGateway\Cost\TokenCounter;
use MageTech\AIGateway\Contracts\CostEstimatorContract;
use MageTech\AIGateway\Contracts\ModelRouterContract;
use MageTech\AIGateway\Contracts\PromptRepositoryContract;
use MageTech\AIGateway\Console\InstallCommand;
use MageTech\AIGateway\Console\PromptMakeCommand;
use MageTech\AIGateway\Console\StatsCommand;
use MageTech\AIGateway\Prompts\PromptManager;
use MageTech\AIGateway\Routing\ModelRouter;

class AIGatewayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mts-ai.php',
            'mts-ai'
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
        $this->app->scoped(Ai::class, function ($app) {
            return new Ai(
                $app->make(PromptManager::class),
                $app->make(ModelRouter::class),
                $app->make(CostEstimator::class)
            );
        });

        $this->app->scoped(PromptManager::class, function ($app) {
            return new PromptManager($app['config']);
        });

        $this->app->scoped(ModelRouter::class, function ($app) {
            return new ModelRouter($app['config']);
        });

        $this->app->scoped(CostEstimator::class, function ($app) {
            return new CostEstimator($app['config']);
        });

        $this->app->scoped(TokenCounter::class, function ($app) {
            return new TokenCounter($app->make(CostEstimator::class));
        });

        $this->app->scoped(PromptRepositoryContract::class, function ($app) {
            return $app->make(PromptManager::class);
        });

        $this->app->scoped(ModelRouterContract::class, function ($app) {
            return $app->make(ModelRouter::class);
        });

        $this->app->scoped(CostEstimatorContract::class, function ($app) {
            return $app->make(CostEstimator::class);
        });
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/mts-ai.php' => config_path('mts-ai.php'),
        ], 'mts-ai-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'mts-ai-migrations');
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
            PromptMakeCommand::class,
            StatsCommand::class,
        ]);
    }
}
