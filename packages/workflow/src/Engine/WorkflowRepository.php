<?php

declare(strict_types=1);

namespace MageTech\Workflow\Engine;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use MageTech\Workflow\Enums\WorkflowStatus;
use MageTech\Workflow\Exceptions\WorkflowNotFoundException;
use MageTech\Workflow\Models\Workflow;
use MageTech\Workflow\Models\WorkflowInstance;

class WorkflowRepository
{
    /**
     * Find a workflow instance by ID.
     */
    public function find(int|string $id): WorkflowInstance
    {
        $instance = WorkflowInstance::with('workflow')->find($id);

        if ($instance === null) {
            throw WorkflowNotFoundException::byId($id);
        }

        return $instance;
    }

    /**
     * Find a workflow definition by name.
     */
    public function findWorkflow(string $name): ?Workflow
    {
        return Workflow::where('name', $name)->first();
    }

    /**
     * Query workflow instances with filters.
     */
    public function query(
        ?string $status = null,
        ?string $workflowableType = null,
        ?int $workflowableId = null,
        ?int $workflowId = null,
        ?string $search = null,
    ): Builder {
        $query = WorkflowInstance::with('workflow');

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($workflowableType !== null) {
            $query->where('workflowable_type', $workflowableType);
        }

        if ($workflowableId !== null) {
            $query->where('workflowable_id', $workflowableId);
        }

        if ($workflowId !== null) {
            $query->where('workflow_id', $workflowId);
        }

        if ($search !== null) {
            $query->where(function ($q) use ($search) {
                $q->where('current_step', 'like', "%{$search}%")
                    ->orWhere('error', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('created_at');
    }

    /**
     * Paginate workflow instances.
     */
    public function paginate(
        int $perPage = 15,
        ?string $status = null,
        ?string $workflowableType = null,
        ?int $workflowableId = null,
        ?int $workflowId = null,
        ?string $search = null,
    ): LengthAwarePaginator {
        return $this->query($status, $workflowableType, $workflowableId, $workflowId, $search)
            ->paginate($perPage);
    }

    /**
     * Get instances ready for retry.
     */
    public function readyForRetry(): \Illuminate\Database\Eloquent\Collection
    {
        return WorkflowInstance::readyForRetry()->get();
    }

    /**
     * Count instances by status.
     */
    public function countByStatus(?int $workflowId = null): array
    {
        $query = WorkflowInstance::query();

        if ($workflowId !== null) {
            $query->where('workflow_id', $workflowId);
        }

        $counts = $query->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $result = [];
        foreach (WorkflowStatus::cases() as $status) {
            $result[$status->value] = $counts[$status->value] ?? 0;
        }

        return $result;
    }

    /**
     * Get all registered workflow definitions.
     */
    public function listWorkflows(): array
    {
        return Workflow::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Prune old workflow records.
     */
    public function prune(int $retainDays = 90): int
    {
        $cutoff = now()->subDays($retainDays);

        $instances = WorkflowInstance::where('created_at', '<', $cutoff)
            ->whereIn('status', [WorkflowStatus::Completed, WorkflowStatus::Cancelled, WorkflowStatus::Failed])
            ->pluck('id');

        $count = $instances->count();

        if ($count > 0) {
            WorkflowInstance::whereIn('id', $instances)->delete();
        }

        return $count;
    }
}
