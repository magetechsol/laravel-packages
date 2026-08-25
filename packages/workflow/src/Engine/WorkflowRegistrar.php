<?php

declare(strict_types=1);

namespace MageTech\Workflow\Engine;

use MageTech\Workflow\Definition\WorkflowDefinition;
use MageTech\Workflow\Exceptions\WorkflowNotFoundException;

class WorkflowRegistrar
{
    /** @var array<string, WorkflowDefinition> */
    private array $definitions = [];

    public function register(WorkflowDefinition $definition): void
    {
        $this->definitions[$definition->getName()] = $definition;
    }

    public function get(string $name): WorkflowDefinition
    {
        if (! isset($this->definitions[$name])) {
            throw WorkflowNotFoundException::byName($name);
        }

        return $this->definitions[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->definitions[$name]);
    }

    /** @return array<string, WorkflowDefinition> */
    public function all(): array
    {
        return $this->definitions;
    }

    public function names(): array
    {
        return array_keys($this->definitions);
    }
}
