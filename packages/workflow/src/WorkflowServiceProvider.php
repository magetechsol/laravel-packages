<?php

declare(strict_types=1);

namespace MageTech\Workflow;

use Illuminate\Support\ServiceProvider;
use MageTech\Workflow\Engine\WorkflowManager;
use MageTech\Workflow\Engine\WorkflowRegistrar;
use MageTech\Workflow\Engine\WorkflowRepository;
use MageTech\Workflow\Engine\WorkflowRunner;
use MageTech\Workflow\Engine\ConditionEvaluator;
use MageTech\Workflow\Engine\ConcurrencyGuard;
use MageTech\Workflow\Approvals\ApprovalManager;
use MageTech\Workflow\Audit\AuditLogger;
use MageTech\Workflow\Support\RetryStrategy;

class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mts-workflow.php',
            'mts-workflow'
        );

        $this->app->scoped(WorkflowRegistrar::class, function () {
            return new WorkflowRegistrar();
        });

        $this->app->scoped(WorkflowRunner::class, function ($app) {
            return new WorkflowRunner(
                $app->make(ConditionEvaluator::class),
                $app->make(ConcurrencyGuard::class),
                $app->make(ApprovalManager::class),
                $app->make(AuditLogger::class),
            );
        });

        $this->app->scoped(WorkflowManager::class, function ($app) {
            return new WorkflowManager(
                $app->make(WorkflowRegistrar::class),
                $app->make(WorkflowRunner::class),
                $app->make(WorkflowRepository::class),
                $app->make(AuditLogger::class),
            );
        });

        $this->app->scoped(WorkflowRepository::class, function () {
            return new WorkflowRepository();
        });

        $this->app->scoped(ConditionEvaluator::class, function () {
            return new ConditionEvaluator();
        });

        $this->app->scoped(ConcurrencyGuard::class, function ($app) {
            return new ConcurrencyGuard();
        });

        $this->app->scoped(ApprovalManager::class, function () {
            return new ApprovalManager();
        });

        $this->app->scoped(AuditLogger::class, function () {
            return new AuditLogger();
        });

        $this->app->scoped(RetryStrategy::class, function () {
            return new RetryStrategy();
        });
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerCommands();
        $this->loadHelpers();
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/mts-workflow.php' => config_path('mts-workflow.php'),
        ], 'mts-workflow-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'mts-workflow-migrations');
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Console\InstallCommand::class,
            Console\MakeWorkflowCommand::class,
            Console\ListWorkflowsCommand::class,
            Console\RunWorkflowCommand::class,
            Console\RetryWorkflowCommand::class,
            Console\CancelWorkflowCommand::class,
        ]);
    }

    protected function loadHelpers(): void
    {
        $helpers = __DIR__ . '/Support/helpers.php';

        if (file_exists($helpers)) {
            require_once $helpers;
        }
    }
}
