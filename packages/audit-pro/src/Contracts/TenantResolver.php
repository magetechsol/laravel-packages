<?php

declare(strict_types=1);

namespace MageTech\Audit\Contracts;

interface TenantResolver
{
    public function resolve(): ?int;

    public function resolveName(): ?string;
}
