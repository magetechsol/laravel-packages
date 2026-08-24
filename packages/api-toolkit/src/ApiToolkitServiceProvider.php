<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit;

use Illuminate\Support\ServiceProvider;
use MageTech\ApiToolkit\Console\InstallCommand;
use MageTech\ApiToolkit\ExceptionHandling\ExceptionMapper;

class ApiToolkitServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mts-api.php',
            'mts-api',
        );

        $this->registerServices();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerCommands();
        $this->registerMiddleware();
    }

    /**
     * Register the service bindings.
     */
    protected function registerServices(): void
    {
        $this->app->scoped(ApiResponseFactory::class, function () {
            return new ApiResponseFactory();
        });

        $this->app->scoped(ExceptionMapper::class, function () {
            return new ExceptionMapper();
        });
    }

    /**
     * Register the publishable resources.
     */
    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/mts-api.php' => config_path('mts-api.php'),
        ], 'mts-api-config');
    }

    /**
     * Register the artisan commands.
     */
    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
        ]);
    }

    /**
     * Register the middleware aliases.
     */
    protected function registerMiddleware(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $router = $this->app['router'];

        if ($router !== null) {
            $router->aliasMiddleware('mts.request_id', \MageTech\ApiToolkit\Middleware\MtsRequestIdMiddleware::class);
            $router->aliasMiddleware('mts.response', \MageTech\ApiToolkit\Middleware\MtsApiResponseMiddleware::class);
        }
    }
}
