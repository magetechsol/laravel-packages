<?php

declare(strict_types=1);

namespace MageTech\Workflow\Definition;

use Closure;
use MageTech\Workflow\Enums\ApprovalType;
use MageTech\Workflow\Enums\RetryBackoff;
use MageTech\Workflow\Enums\StepType;

class WorkflowStepDefinition
{
    public function __construct(
        private string $name,
        private StepType $type,
        private ?string $handler = null,
        private ?ApprovalType $approvalType = null,
        private ?Closure $condition = null,
        private array $approvers = [],
        private ?string $approverRole = null,
        private int $timeout = 300,
        private int $maxAttempts = 3,
        private RetryBackoff $backoff = RetryBackoff::Exponential,
        private int $baseDelay = 60,
        private bool $queued = false,
        private int $order = 0,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): StepType
    {
        return $this->type;
    }

    public function getHandler(): ?string
    {
        return $this->handler;
    }

    public function getApprovalType(): ?ApprovalType
    {
        return $this->approvalType;
    }

    public function getCondition(): ?Closure
    {
        return $this->condition;
    }

    /** @return array<int, int|string> */
    public function getApprovers(): array
    {
        return $this->approvers;
    }

    public function getApproverRole(): ?string
    {
        return $this->approverRole;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function getBackoff(): RetryBackoff
    {
        return $this->backoff;
    }

    public function getBaseDelay(): int
    {
        return $this->baseDelay;
    }

    public function isQueued(): bool
    {
        return $this->queued;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type->value,
            'handler' => $this->handler,
            'approval_type' => $this->approvalType?->value,
            'approvers' => $this->approvers,
            'approver_role' => $this->approverRole,
            'timeout' => $this->timeout,
            'max_attempts' => $this->maxAttempts,
            'backoff' => $this->backoff->value,
            'base_delay' => $this->baseDelay,
            'queued' => $this->queued,
            'order' => $this->order,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            type: StepType::from($data['type']),
            handler: $data['handler'] ?? null,
            approvalType: isset($data['approval_type']) ? ApprovalType::from($data['approval_type']) : null,
            condition: null,
            approvers: $data['approvers'] ?? [],
            approverRole: $data['approver_role'] ?? null,
            timeout: $data['timeout'] ?? 300,
            maxAttempts: $data['max_attempts'] ?? 3,
            backoff: RetryBackoff::from($data['backoff'] ?? 'exponential'),
            baseDelay: $data['base_delay'] ?? 60,
            queued: $data['queued'] ?? false,
            order: $data['order'] ?? 0,
        );
    }
}
