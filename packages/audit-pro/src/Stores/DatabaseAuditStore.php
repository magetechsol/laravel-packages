<?php

declare(strict_types=1);

namespace MageTech\Audit\Stores;

use Illuminate\Support\Facades\DB;
use MageTech\Audit\Contracts\AuditQuery;
use MageTech\Audit\Contracts\AuditStore;
use MageTech\Audit\Support\AuditData;
use MageTech\Audit\Support\DatabaseAuditQuery;

class DatabaseAuditStore implements AuditStore
{
    protected string $table;

    public function __construct()
    {
        $this->table = config('audit.table', 'audits');
    }

    public function record(AuditData $data): void
    {
        $connection = config('audit.connection');

        DB::connection($connection)
            ->table($this->table)
            ->insert($this->prepareRecord($data));
    }

    public function recordBatch(array $records): void
    {
        if (empty($records)) {
            return;
        }

        $connection = config('audit.connection');
        $prepared = array_map(fn (AuditData $data) => $this->prepareRecord($data), $records);

        $chunks = array_chunk($prepared, 100);

        DB::connection($connection)->beginTransaction();

        try {
            foreach ($chunks as $chunk) {
                DB::connection($connection)
                    ->table($this->table)
                    ->insert($chunk);
            }

            DB::connection($connection)->commit();
        } catch (\Exception $e) {
            DB::connection($connection)->rollBack();

            throw $e;
        }
    }

    public function query(): AuditQuery
    {
        $connection = config('audit.connection');

        return new DatabaseAuditQuery(
            DB::connection($connection)->table($this->table)
        );
    }

    protected function prepareRecord(AuditData $data): array
    {
        $record = $data->toArray();

        $record['created_at'] = now();

        return array_filter($record, fn ($v) => $v !== null);
    }
}
