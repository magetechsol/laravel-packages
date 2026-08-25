<?php

declare(strict_types=1);

if (! function_exists('workflow')) {
    /**
     * Get the workflow manager instance.
     */
    function workflow(): \MageTech\Workflow\Engine\WorkflowManager
    {
        return app(\MageTech\Workflow\Engine\WorkflowManager::class);
    }
}

if (! function_exists('workflow_define')) {
    /**
     * Define a new workflow.
     */
    function workflow_define(string $name): \MageTech\Workflow\Definition\WorkflowDefinition
    {
        return \MageTech\Workflow\Definition\WorkflowDefinition::define($name);
    }
}

if (! function_exists('workflow_start')) {
    /**
     * Start a workflow instance.
     */
    function workflow_start(
        string $workflowName,
        mixed $model,
        ?int $startedBy = null,
        ?string $requestId = null,
        ?array $initialContext = null,
    ): \MageTech\Workflow\Models\WorkflowInstance {
        return workflow()->start($workflowName, $model, $startedBy, $requestId, $initialContext);
    }
}

if (! function_exists('workflow_retry_delay')) {
    /**
     * Calculate the retry delay for a given attempt.
     */
    function workflow_retry_delay(
        int $attempt,
        string $backoff = 'exponential',
        int $baseDelay = 60,
        int $maxDelay = 3600,
    ): int {
        $strategy = new \MageTech\Workflow\Support\RetryStrategy();
        $backoffEnum = \MageTech\Workflow\Enums\RetryBackoff::from($backoff);

        return $strategy->calculateDelay($attempt, $backoffEnum, $baseDelay, $maxDelay);
    }
}
