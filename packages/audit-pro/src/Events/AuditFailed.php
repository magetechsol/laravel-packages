<?php

declare(strict_types=1);

namespace MageTech\Audit\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MageTech\Audit\Support\AuditData;

class AuditFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public AuditData $data,
        public \Throwable $exception,
    ) {}
}
