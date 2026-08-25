<?php

declare(strict_types=1);

namespace MageTech\Workflow\Console;

use Illuminate\Console\Command;
use MageTech\Workflow\Engine\WorkflowManager;

class RunWorkflowCommand extends Command
{
    protected $signature = 'mts:workflow:run {id}';

    protected $description = 'Manually run/retry a workflow instance';

    public function handle(WorkflowManager $manager): int
    {
        $id = $this->argument('id');

        try {
            $instance = $manager->get($id);

            if (! $instance->canRetry()) {
                $this->error("Workflow instance [{$id}] cannot be run (status: {$instance->status->value}).");

                return self::FAILURE;
            }

            $instance = $manager->retry($id);

            $this->info("Workflow instance [{$id}] restarted successfully.");
            $this->line("Status: {$instance->status->value}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to run workflow: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
