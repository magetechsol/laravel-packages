<?php

declare(strict_types=1);

namespace MageTech\SaaS\Isolation;

use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Storage;

class TenantStorageManager
{
    public function __construct(
        protected Repository $config,
    ) {}

    public function getDiskName(?string $tenantId = null): string
    {
        $tenantId = $tenantId ?? tenant_id();
        $strategy = $this->config->get('mts-saas.storage.strategy', 'prefix');

        if ($strategy === 'disk') {
            return "tenant_{$tenantId}";
        }

        return $this->config->get('mts-saas.storage.disk', 'local');
    }

    public function getPrefix(?string $tenantId = null): string
    {
        $tenantId = $tenantId ?? tenant_id();
        $prefix = $this->config->get('mts-saas.storage.prefix', 'tenants');

        return "{$prefix}/{$tenantId}";
    }

    public function path(?string $path = null, ?string $tenantId = null): string
    {
        $prefix = $this->getPrefix($tenantId);

        return $path ? "{$prefix}/{$path}" : $prefix;
    }

    public function disk(?string $tenantId = null)
    {
        $disk = $this->getDiskName($tenantId);

        return Storage::disk($disk);
    }

    public function put(string $path, $contents, ?string $visibility = null): bool
    {
        return $this->disk()->put($this->path($path), $contents, $visibility);
    }

    public function get(string $path): ?string
    {
        return $this->disk()->get($this->path($path));
    }

    public function delete(string $path): bool
    {
        return $this->disk()->delete($this->path($path));
    }

    public function exists(string $path): bool
    {
        return $this->disk()->exists($this->path($path));
    }

    public function url(string $path): string
    {
        return $this->disk()->url($this->path($path));
    }

    public function allFiles(?string $directory = null): array
    {
        return $this->disk()->allFiles($this->path($directory));
    }
}
