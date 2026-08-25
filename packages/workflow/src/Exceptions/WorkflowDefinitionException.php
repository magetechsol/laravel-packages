<?php

declare(strict_types=1);

namespace MageTech\Workflow\Exceptions;

class WorkflowDefinitionException extends WorkflowException
{
    public static function duplicateStep(string $name): static
    {
        return new static("Workflow step [{$name}] is already defined.");
    }

    public static function missingStep(string $name): static
    {
        return new static("Referenced step [{$name}] does not exist in the workflow definition.");
    }

    public static function invalidDefinition(string $reason): static
    {
        return new static("Invalid workflow definition: {$reason}");
    }

    public static function emptyWorkflow(): static
    {
        return new static('Workflow must have at least one step.');
    }
}
