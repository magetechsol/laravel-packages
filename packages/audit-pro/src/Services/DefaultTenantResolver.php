<?php

declare(strict_types=1);

namespace MageTech\Audit\Services;

use MageTech\Audit\Contracts\TenantResolver;

class DefaultTenantResolver implements TenantResolver
{
    public function resolve(): ?int
    {
        if (!config('audit.tenancy.enabled', false)) {
            return null;
        }

        if (function_exists('tenant')) {
            return tenant('id');
        }

        $resolverClass = config('audit.tenancy.resolver');

        if ($resolverClass && class_exists($resolverClass) && $resolverClass !== self::class) {
            return app($resolverClass)->resolve();
        }

        return null;
    }

    public function resolveName(): ?string
    {
        if (function_exists('tenant')) {
            return tenant('name');
        }

        return null;
    }
}
