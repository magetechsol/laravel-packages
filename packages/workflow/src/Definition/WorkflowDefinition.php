<?php

declare(strict_types=1);

namespace MageTech\Workflow\Definition;

use Closure;
use MageTech\Workflow\Enums\ApprovalType;
use MageTech\Workflow\Enums\RetryBackoff;
use MageTech\Workflow\Enums\StepType;
use MageTech\Workflow\Exceptions\WorkflowDefinitionException;

class WorkflowDefinition
{
    private string $name;

    private ?string $description = null;

    /** @var array<int, WorkflowStepDefinition> */
    private array $steps = [];

    private int $stepOrder = 0;

    private ?Closure $whenCondition = null;

    private ?Closure $unlessCondition = null;

    private ?string $currentStepName = null;

    /** @var array<int, WorkflowStepBuilder> */
    private array $pendingBuilders = [];

    private bool $built = false;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function define(string $name): static
    {
        return new static($name);
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Add an action step to the workflow.
     */
    public function step(string $name, ?string $handler = null): WorkflowStepBuilder
    {
        $this->flushPendingBuilders();
        $builder = (new WorkflowStepBuilder($name, StepType::Action, $handler))
            ->setDefinition($this);
        $this->currentStepName = $name;
        $this->pendingBuilders[] = $builder;

        return $builder;
    }

    /**
     * Add an approval step to the workflow.
     */
    public function approval(string $name, ?ApprovalType $type = null): WorkflowStepBuilder
    {
        $this->flushPendingBuilders();
        $builder = (new WorkflowStepBuilder($name, StepType::Approval))
            ->approvalType($type ?? ApprovalType::Single)
            ->setDefinition($this);
        $this->currentStepName = $name;
        $this->pendingBuilders[] = $builder;

        return $builder;
    }

    /**
     * Add a condition step (conditional branching).
     */
    public function condition(string $name, Closure $condition): WorkflowStepBuilder
    {
        $this->flushPendingBuilders();
        $builder = (new WorkflowStepBuilder($name, StepType::Condition))
            ->condition($condition)
            ->setDefinition($this);
        $this->currentStepName = $name;
        $this->pendingBuilders[] = $builder;

        return $builder;
    }

    private function flushPendingBuilders(): void
    {
        foreach ($this->pendingBuilders as $builder) {
            $this->steps[] = $builder->build();
        }
        $this->pendingBuilders = [];
    }

    /**
     * Mark the workflow as complete.
     */
    public function complete(): static
    {
        $this->flushPendingBuilders();
        $this->steps[] = (new WorkflowStepBuilder('__complete', StepType::Complete))->build();

        return $this;
    }

    /**
     * Set a global "when" condition for the workflow.
     */
    public function when(Closure $condition): static
    {
        $this->whenCondition = $condition;

        return $this;
    }

    /**
     * Set a global "unless" condition for the workflow.
     */
    public function unless(Closure $condition): static
    {
        $this->unlessCondition = $condition;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /** @return array<int, WorkflowStepDefinition> */
    public function getSteps(): array
    {
        $this->flushPendingBuilders();

        return $this->steps;
    }

    public function getWhenCondition(): ?Closure
    {
        return $this->whenCondition;
    }

    public function getUnlessCondition(): ?Closure
    {
        return $this->unlessCondition;
    }

    public function validate(): void
    {
        $this->flushPendingBuilders();

        if ($this->steps === []) {
            throw WorkflowDefinitionException::emptyWorkflow();
        }

        $names = [];
        foreach ($this->steps as $step) {
            $name = $step->getName();
            if ($name !== '__complete' && in_array($name, $names, true)) {
                throw WorkflowDefinitionException::duplicateStep($name);
            }
            $names[] = $name;
        }
    }

    /**
     * Serialize the definition to an array for database storage.
     */
    public function toArray(): array
    {
        $this->flushPendingBuilders();

        return [
            'name' => $this->name,
            'description' => $this->description,
            'steps' => array_map(fn (WorkflowStepDefinition $step) => $step->toArray(), $this->steps),
        ];
    }

    /**
     * Reconstruct a definition from a stored array.
     */
    public static function fromArray(array $data): static
    {
        $definition = new static($data['name']);
        $definition->description = $data['description'] ?? null;

        foreach ($data['steps'] as $stepData) {
            $definition->steps[] = WorkflowStepDefinition::fromArray($stepData);
        }

        return $definition;
    }
}
