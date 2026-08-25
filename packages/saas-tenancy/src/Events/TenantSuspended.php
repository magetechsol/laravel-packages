<?php

declare(strict_types=1);

namespace MageTech\SaaS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use MageTech\SaaS\Models\Tenant;

class TenantSuspended
{
    use Dispatchable;

    public function __construct(
        public readonly Tenant $tenant,
    ) {}
}
