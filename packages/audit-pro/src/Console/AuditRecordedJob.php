<?php

declare(strict_types=1);

namespace MageTech\Audit\Console;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MageTech\Audit\Contracts\AuditStore;
use MageTech\Audit\Support\AuditData;

class AuditRecordedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 3;

    public function __construct(
        public AuditData $data,
    ) {
        $this->queue = config('audit.queue_name', 'audit');
        $this->connection = config('audit.queue_connection');
    }

    public function handle(AuditStore $store): void
    {
        $store->record($this->data);
    }

    public function failed(\Throwable $exception): void
    {
        event(new \MageTech\Audit\Events\AuditFailed(
            data: $this->data,
            exception: $exception,
        ));
    }
}
