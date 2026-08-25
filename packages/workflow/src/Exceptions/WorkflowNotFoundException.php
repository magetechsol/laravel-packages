<?php

declare(strict_types=1);

namespace MageTech\Workflow\Exceptions;

class WorkflowNotFoundException extends WorkflowException
{
    public static function byName(string $name): static
    {
        return new static("Workflow definition [{$name}] not found.");
    }

    public static function byId(int|string $id): static
    {
        return new static("Workflow instance [{$id}] not found.");
    }
}
