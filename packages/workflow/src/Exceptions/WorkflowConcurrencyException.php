<?php

declare(strict_types=1);

namespace MageTech\Workflow\Exceptions;

class WorkflowConcurrencyException extends WorkflowException
{
    public static function stepLocked(string $stepName, int|string $instanceId): static
    {
        return new static("Workflow step [{$stepName}] for instance [{$instanceId}] is already being executed.");
    }

    public static function instanceLocked(int|string $instanceId): static
    {
        return new static("Workflow instance [{$instanceId}] is currently locked by another process.");
    }
}
