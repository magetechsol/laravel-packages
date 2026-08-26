<?php

declare(strict_types=1);

namespace MageTech\Audit\Contracts;

interface AuditQuery
{
    public function whereEvent(string $event): self;

    public function whereActor(string $type, int|string|null $id = null): self;

    public function whereModel(string $type, int|string|null $id = null): self;

    public function whereTenant(int|string|null $tenantId): self;

    public function whereDateRange(string $from, string $to): self;

    public function whereIp(string $ip): self;

    public function whereRequestId(string $requestId): self;

    public function whereBatch(string $batchUuid): self;

    public function whereTag(string $tag): self;

    public function whereAction(string $action): self;

    public function latest(): self;

    public function oldest(): self;

    public function paginate(int $perPage = 15);

    public function get();

    public function first();

    public function count(): int;
}
