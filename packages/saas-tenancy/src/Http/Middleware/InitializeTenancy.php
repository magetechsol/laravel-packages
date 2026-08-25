<?php

declare(strict_types=1);

namespace MageTech\SaaS\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use MageTech\SaaS\Support\Facades\Tenant;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancy
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Tenant::getTenant();

        if ($tenant) {
            app()->singleton('current_tenant', fn () => $tenant);

            $this->configureCache($tenant);
            $this->configureQueue($tenant);
            $this->configureStorage($tenant);
        }

        $response = $next($request);

        if ($tenant) {
            Tenant::reset();
        }

        return $response;
    }

    protected function configureCache($tenant): void
    {
        if (! config('mts-saas.cache.enabled', true)) {
            return;
        }

        $prefix = config('mts-saas.cache.prefix', 'tenant');
        $tenantId = $tenant->getKey();

        config(['cache.prefix' => "{$prefix}_{$tenantId}"]);
    }

    protected function configureQueue($tenant): void
    {
        if (! config('mts-saas.queue.enabled', true)) {
            return;
        }

        $prefix = config('mts-saas.queue.prefix', 'tenant');
        $tenantId = $tenant->getKey();

        config(['queue.prefix' => "{$prefix}_{$tenantId}"]);
    }

    protected function configureStorage($tenant): void
    {
        if (! config('mts-saas.storage.enabled', true)) {
            return;
        }

        $strategy = config('mts-saas.storage.strategy', 'prefix');
        $disk = config('mts-saas.storage.disk', 'local');
        $prefix = config('mts-saas.storage.prefix', 'tenants');
        $tenantId = $tenant->getKey();

        if ($strategy === 'prefix') {
            config([
                "filesystems.disks.tenant" => array_merge(
                    config("filesystems.disks.{$disk}") ?? [],
                    ['root' => storage_path("app/{$prefix}/{$tenantId}")]
                ),
            ]);
        }
    }
}
