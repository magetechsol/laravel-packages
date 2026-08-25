<?php

declare(strict_types=1);

namespace MageTech\Workflow\Definition;

use Closure;
use MageTech\Workflow\Enums\ApprovalType;
use MageTech\Workflow\Enums\RetryBackoff;
use MageTech\Workflow\Enums\StepType;

class WorkflowStepBuilder
{
    private string $name;

    private StepType $type;

    private ?string $handler;

    private ?ApprovalType $approvalType = null;

    private ?Closure $condition = null;

    private array $approvers = [];

    private ?string $approverRole = null;

    private int $timeout = 300;

    private int $maxAttempts = 3;

    private RetryBackoff $backoff = RetryBackoff::Exponential;

    private int $baseDelay = 60;

    private bool $queued = false;

    private ?WorkflowDefinition $definition = null;

    public function __construct(string $name, StepType $type, ?string $handler = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->handler = $handler;
    }

    public function setDefinition(WorkflowDefinition $definition): static
    {
        $this->definition = $definition;

        return $this;
    }

    public function step(string $name, ?string $handler = null): WorkflowStepBuilder
    {
        return $this->definition->step($name, $handler);
    }

    public function approval(string $name, ?ApprovalType $type = null): WorkflowStepBuilder
    {
        return $this->definition->approval($name, $type);
    }

    public function condition(string $name, Closure $condition): WorkflowStepBuilder
    {
        return $this->definition->condition($name, $condition);
    }

    public function complete(): WorkflowDefinition
    {
        return $this->definition->complete();
    }

    public function handler(string $handler): static
    {
        $this->handler = $handler;

        return $this;
    }

    public function timeout(int $seconds): static
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function maxAttempts(int $attempts): static
    {
        $this->maxAttempts = $attempts;

        return $this;
    }

    public function backoff(RetryBackoff $backoff): static
    {
        $this->backoff = $backoff;

        return $this;
    }

    public function baseDelay(int $seconds): static
    {
        $this->baseDelay = $seconds;

        return $this;
    }

    public function when(Closure $condition): static
    {
        $this->condition = $condition;

        return $this;
    }

    public function queued(bool $queued = true): static
    {
        $this->queued = $queued;

        return $this;
    }

    public function approvalType(ApprovalType $type): static
    {
        $this->approvalType = $type;

        return $this;
    }

    /** @param  array<int, int|string>  $approvers */
    public function approvers(array $approvers): static
    {
        $this->approvers = $approvers;

        return $this;
    }

    public function approverRole(string $role): static
    {
        $this->approverRole = $role;

        return $this;
    }

    public function build(): WorkflowStepDefinition
    {
        return new WorkflowStepDefinition(
            name: $this->name,
            type: $this->type,
            handler: $this->handler,
            approvalType: $this->approvalType,
            condition: $this->condition,
            approvers: $this->approvers,
            approverRole: $this->approverRole,
            timeout: $this->timeout,
            maxAttempts: $this->maxAttempts,
            backoff: $this->backoff,
            baseDelay: $this->baseDelay,
            queued: $this->queued,
        );
    }
}
