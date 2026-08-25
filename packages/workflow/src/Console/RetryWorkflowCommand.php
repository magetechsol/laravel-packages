<?php

declare(strict_types=1);

namespace MageTech\Workflow\Console;

use Illuminate\Console\Command;
use MageTech\Workflow\Engine\WorkflowManager;

class RetryWorkflowCommand extends Command
{
    protected $signature = 'mts:workflow:retry {id}';

    protected $description = 'Retry a failed workflow instance';

    public function handle(WorkflowManager $manager): int
    {
        $id = $this->argument('id');

        try {
            $instance = $manager->get($id);

            if (! $instance->canRetry()) {
                $this->error("Workflow instance [{$id}] cannot be retried (status: {$instance->status->value}).");

                return self::FAILURE;
            }

            $instance = $manager->retry($id);

            $this->info("Workflow instance [{$id}] retry initiated.");
            $this->line("Status: {$instance->status->value}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to retry workflow: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
