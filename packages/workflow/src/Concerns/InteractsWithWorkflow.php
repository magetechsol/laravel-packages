<?php

declare(strict_types=1);

namespace MageTech\Workflow\Concerns;

use MageTech\Workflow\Engine\WorkflowManager;
use MageTech\Workflow\Models\WorkflowInstance;

trait InteractsWithWorkflow
{
    /**
     * Get all workflow instances for this model.
     */
    public function workflowInstances()
    {
        return $this->morphMany(WorkflowInstance::class, 'workflowable');
    }

    /**
     * Start a workflow for this model.
     */
    public function startWorkflow(
        string $workflowName,
        ?int $startedBy = null,
        ?string $requestId = null,
        ?array $initialContext = null,
    ): WorkflowInstance {
        return app(WorkflowManager::class)->start(
            $workflowName,
            $this,
            $startedBy,
            $requestId,
            $initialContext,
        );
    }

    /**
     * Get the active workflow instance for this model.
     */
    public function activeWorkflow(): ?WorkflowInstance
    {
        return $this->workflowInstances()
            ->whereIn('status', ['draft', 'running', 'paused'])
            ->latest()
            ->first();
    }

    /**
     * Check if this model has an active workflow.
     */
    public function hasActiveWorkflow(): bool
    {
        return $this->activeWorkflow() !== null;
    }
}
