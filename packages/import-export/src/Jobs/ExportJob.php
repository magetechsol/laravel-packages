<?php

declare(strict_types=1);

namespace MageTech\ImportExport\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MageTech\ImportExport\Exporters\Exporter;
use MageTech\ImportExport\Models\Export;

class ExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 600;

    public int $tries = 3;

    public function __construct(
        public Export $export,
    ) {
        $this->queue = config('mts-import-export.export.queue_name', 'exports');
        $this->connection = config('mts-import-export.export.queue_connection', 'redis');
        $this->timeout = config('mts-import-export.export.timeout', 600);
        $this->tries = config('mts-import-export.export.tries', 3);
    }

    public function handle(): void
    {
        $exporter = new Exporter(
            modelClass: $this->export->model_class,
            columns: $this->export->columns ?? [],
            disk: config('mts-import-export.disk', 'local'),
            chunkSize: config('mts-import-export.export.chunk_size', 1000),
        );

        $exporter->process($this->export);
    }

    public function failed(\Throwable $exception): void
    {
        $this->export->markAsFailed($exception->getMessage());
    }
}
