<?php

declare(strict_types=1);

namespace MageTech\Workflow\Console;

use Illuminate\Console\Command;
use MageTech\Workflow\Engine\WorkflowManager;

class CancelWorkflowCommand extends Command
{
    protected $signature = 'mts:workflow:cancel {id} {--reason=}';

    protected $description = 'Cancel a running workflow instance';

    public function handle(WorkflowManager $manager): int
    {
        $id = $this->argument('id');
        $reason = $this->option('reason');

        try {
            $instance = $manager->get($id);

            if (! $instance->canCancel()) {
                $this->error("Workflow instance [{$id}] cannot be cancelled (status: {$instance->status->value}).");

                return self::FAILURE;
            }

            $instance = $manager->cancel($id, reason: $reason);

            $this->info("Workflow instance [{$id}] cancelled successfully.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to cancel workflow: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
