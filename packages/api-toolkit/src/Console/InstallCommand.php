<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'mts:api:install', description: 'Install the MTS API Toolkit configuration')]
class InstallCommand extends Command
{
    protected $signature = 'mts:api:install';

    protected $description = 'Install the MTS API Toolkit configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing MTS API Toolkit...');

        $this->publishConfig();

        $this->registerMiddleware();

        $this->registerExceptionHandler();

        $this->info('MTS API Toolkit installed successfully!');

        $this->newLine();

        $this->info('Next steps:');
        $this->line('  1. Review config/mts-api.php');
        $this->line('  2. Add middleware to your routes (app/Http/Kernel.php or bootstrap/app.php)');
        $this->line('  3. Use ApiResponse facade in your controllers');

        return Command::SUCCESS;
    }

    /**
     * Publish the configuration file.
     */
    protected function publishConfig(): void
    {
        $source = __DIR__ . '/../../config/mts-api.php';
        $destination = config_path('mts-api.php');

        if (File::exists($destination)) {
            $this->warn('Configuration file already exists. Skipping...');
            return;
        }

        File::copy($source, $destination);

        $this->info('Configuration published to config/mts-api.php');
    }

    /**
     * Register middleware aliases.
     */
    protected function registerMiddleware(): void
    {
        $this->info('Middleware registration guide:');
        $this->line('  Add to your middleware aliases in app/Http/Kernel.php:');
        $this->line('  protected $middlewareAliases = [');
        $this->line('      ...');
        $this->line('      \'mts.request_id\' => \MageTech\ApiToolkit\Middleware\MtsRequestIdMiddleware::class,');
        $this->line('      \'mts.response\' => \MageTech\ApiToolkit\Middleware\MtsApiResponseMiddleware::class,');
        $this->line('  ];');
        $this->newLine();
        $this->line('  Or in bootstrap/app.php with Laravel 11+:');
        $this->line('  ->withMiddleware(function (Middleware $middleware) {');
        $this->line('      $middleware->alias([');
        $this->line('          \'mts.request_id\' => \MageTech\ApiToolkit\Middleware\MtsRequestIdMiddleware::class,');
        $this->line('          \'mts.response\' => \MageTech\ApiToolkit\Middleware\MtsApiResponseMiddleware::class,');
        $this->line('      ]);');
        $this->line('  })');
    }

    /**
     * Register exception handler.
     */
    protected function registerExceptionHandler(): void
    {
        $this->info('Exception handler registration guide:');
        $this->line('  To use custom exception handling, register in your ExceptionHandler:');
        $this->line('  In bootstrap/app.php with Laravel 11+:');
        $this->line('  ->withExceptions(function (Exceptions $exceptions) {');
        $this->line('      $exceptions->shouldRender(function (\Throwable $e) {');
        $this->line('          if (\MageTech\ApiToolkit\ExceptionHandling\ExceptionMapper::isEnabled()) {');
        $this->line('              return true;');
        $this->line('          }');
        $this->line('          return false;');
        $this->line('      });');
        $this->line('  })');
    }
}
