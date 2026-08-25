<?php

declare(strict_types=1);

namespace MageTech\Workflow\Exceptions;

class WorkflowStepException extends WorkflowException
{
    public static function handlerNotFound(string $handler): static
    {
        return new static("Workflow step handler [{$handler}] not found.");
    }

    public static function handlerFailed(string $handler, string $reason): static
    {
        return new static("Workflow step handler [{$handler}] failed: {$reason}");
    }

    public static function stepTimeout(string $stepName, int $timeout): static
    {
        return new static("Workflow step [{$stepName}] timed out after {$timeout} seconds.");
    }

    public static function maxAttemptsReached(string $stepName, int $maxAttempts): static
    {
        return new static("Workflow step [{$stepName}] has reached maximum attempts ({$maxAttempts}).");
    }
}
