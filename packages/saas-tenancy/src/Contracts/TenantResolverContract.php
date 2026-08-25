<?php

declare(strict_types=1);

namespace MageTech\SaaS\Contracts;

use MageTech\SaaS\Models\Tenant;

interface TenantResolverContract
{
    public function resolve(): ?Tenant;

    public function getResolved(): ?Tenant;

    public function setResolved(Tenant $tenant): void;

    public function flush(): void;
}
