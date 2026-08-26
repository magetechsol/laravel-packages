<?php

declare(strict_types=1);

namespace MageTech\Audit\Contracts;

use Illuminate\Database\Eloquent\Model;

interface AuditStore
{
    public function record(AuditData $data): void;

    public function recordBatch(array $records): void;

    public function query(): AuditQuery;
}
