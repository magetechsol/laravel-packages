<?php

declare(strict_types=1);

namespace MageTech\Audit\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuditStored
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public int $auditId,
        public string $uuid,
    ) {}
}
