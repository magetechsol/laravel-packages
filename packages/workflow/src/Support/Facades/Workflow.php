<?php

declare(strict_types=1);

namespace MageTech\Workflow\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \MageTech\Workflow\Definition\WorkflowDefinition define(string $name)
 * @method static \MageTech\Workflow\Models\WorkflowInstance start(string $workflowName, mixed $model, ?int $startedBy = null, ?string $requestId = null, ?array $initialContext = null)
 * @method static \MageTech\Workflow\Models\WorkflowInstance approve(int|string $instanceId, string $stepName, ?int $approverId = null, ?string $comment = null)
 * @method static \MageTech\Workflow\Models\WorkflowInstance reject(int|string $instanceId, string $stepName, ?int $approverId = null, ?string $comment = null)
 * @method static \MageTech\Workflow\Models\WorkflowInstance cancel(int|string $instanceId, ?int $actorId = null, ?string $reason = null)
 * @method static \MageTech\Workflow\Models\WorkflowInstance retry(int|string $instanceId)
 * @method static \MageTech\Workflow\Models\WorkflowInstance get(int|string $instanceId)
 * @method static \Illuminate\Contracts\Pagination\LengthAwarePaginator paginate(int $perPage = 15, ?string $status = null, ?string $workflowableType = null, ?int $workflowableId = null, ?int $workflowId = null, ?string $search = null)
 *
 * @see \MageTech\Workflow\Engine\WorkflowManager
 */
class Workflow extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MageTech\Workflow\Engine\WorkflowManager::class;
    }
}
