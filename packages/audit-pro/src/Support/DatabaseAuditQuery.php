<?php

declare(strict_types=1);

namespace MageTech\Audit\Support;

use Illuminate\Database\Query\Builder;
use MageTech\Audit\Contracts\AuditQuery;

class DatabaseAuditQuery implements AuditQuery
{
    public function __construct(
        protected Builder $query,
    ) {}

    public function whereEvent(string $event): static
    {
        $this->query->where('event', $event);

        return $this;
    }

    public function whereActor(string $type, int|string|null $id = null): static
    {
        $this->query->where('actor_type', $type);

        if ($id !== null) {
            $this->query->where('actor_id', $id);
        }

        return $this;
    }

    public function whereModel(string $type, int|string|null $id = null): static
    {
        $this->query->where('auditable_type', $type);

        if ($id !== null) {
            $this->query->where('auditable_id', $id);
        }

        return $this;
    }

    public function whereTenant(int|string|null $tenantId): static
    {
        $this->query->where('tenant_id', $tenantId);

        return $this;
    }

    public function whereDateRange(string $from, string $to): static
    {
        $this->query->whereBetween('created_at', [$from, $to]);

        return $this;
    }

    public function whereIp(string $ip): static
    {
        $this->query->where('ip_address', $ip);

        return $this;
    }

    public function whereRequestId(string $requestId): static
    {
        $this->query->where('request_id', $requestId);

        return $this;
    }

    public function whereBatch(string $batchUuid): static
    {
        $this->query->where('batch_uuid', $batchUuid);

        return $this;
    }

    public function whereTag(string $tag): static
    {
        $this->query->whereJsonContains('tags', $tag);

        return $this;
    }

    public function whereAction(string $action): static
    {
        $this->query->where('action', $action);

        return $this;
    }

    public function latest(): static
    {
        $this->query->orderByDesc('created_at');

        return $this;
    }

    public function oldest(): static
    {
        $this->query->orderBy('created_at');

        return $this;
    }

    public function paginate(int $perPage = 15)
    {
        $page = request()->input('page', 1);

        $total = (clone $this->query)->count();

        $results = $this->query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return new \Illuminate\Pipelines\Pipeline(
            app('request'),
            [
                'data' => $results,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => (int) $page,
                'last_page' => (int) ceil($total / $perPage),
            ]
        );
    }

    public function get()
    {
        return $this->query->get();
    }

    public function first()
    {
        return $this->query->first();
    }

    public function count(): int
    {
        return (int) $this->query->count();
    }

    public function toSql(): string
    {
        return $this->query->toSql();
    }
}
