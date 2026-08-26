<?php

declare(strict_types=1);

namespace MageTech\Audit\Contracts;

use Illuminate\Support\Collection;

interface AuditExporter
{
    public function toCsv($query, array $columns = []): string;

    public function toJson($query, array $columns = []): string;

    public function toArray($query, array $columns = []): array;
}
