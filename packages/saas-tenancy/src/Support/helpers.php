<?php

declare(strict_types=1);

use MageTech\SaaS\Support\Facades\Tenant;

if (! function_exists('tenant')) {
    /**
     * Get the current tenant or a tenant property.
     *
     * @param  string|null  $key
     * @return \MageTech\SaaS\Models\Tenant|string|null
     */
    function tenant(?string $key = null): mixed
    {
        $tenant = Tenant::getTenant();

        if ($key === null) {
            return $tenant;
        }

        return $tenant?->{$key};
    }
}

if (! function_exists('tenant_id')) {
    /**
     * Get the current tenant ID.
     */
    function tenant_id(): ?string
    {
        return Tenant::getTenantId();
    }
}

if (! function_exists('tenant_key')) {
    /**
     * Get the current tenant key value.
     */
    function tenant_key(): ?string
    {
        return Tenant::getTenantKey();
    }
}

if (! function_exists('tenant_route')) {
    /**
     * Generate a URL with the current tenant prefix.
     */
    function tenant_route(string $route, array $parameters = [], bool $absolute = true): string
    {
        $tenantId = tenant_id();

        if (! $tenantId) {
            return route($route, $parameters, $absolute);
        }

        return route($route, array_merge(['tenant' => $tenantId], $parameters), $absolute);
    }
}

if (! function_exists('tenant_storage_path')) {
    /**
     * Get the storage path for the current tenant.
     */
    function tenant_storage_path(?string $path = null): string
    {
        $tenantId = tenant_id();
        $prefix = config('mts-saas.storage.prefix', 'tenants');

        $base = $path
            ? "{$prefix}/{$tenantId}/{$path}"
            : "{$prefix}/{$tenantId}";

        return storage_path($base);
    }
}

if (! function_exists('tenant_cache_key')) {
    /**
     * Generate a cache key prefixed with the current tenant ID.
     */
    function tenant_cache_key(string $key): string
    {
        $tenantId = tenant_id();
        $prefix = config('mts-saas.cache.prefix', 'tenant');

        return "{$prefix}:{$tenantId}:{$key}";
    }
}
