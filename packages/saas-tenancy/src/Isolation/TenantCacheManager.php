<?php

declare(strict_types=1);

namespace MageTech\SaaS\Isolation;

use Illuminate\Config\Repository;

class TenantCacheManager
{
    public function __construct(
        protected Repository $config,
    ) {}

    public function prefix(?string $tenantId = null): string
    {
        $tenantId = $tenantId ?? tenant_id();
        $prefix = $this->config->get('mts-saas.cache.prefix', 'tenant');

        return "{$prefix}:{$tenantId}";
    }

    public function key(string $key): string
    {
        return $this->prefix() . ":{$key}";
    }

    public function store(): string
    {
        return $this->config->get('cache.default', 'file');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return cache()->store($this->store())->get($this->key($key), $default);
    }

    public function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        return cache()->store($this->store())->put($this->key($key), $value, $ttl);
    }

    public function forget(string $key): bool
    {
        return cache()->store($this->store())->forget($this->key($key));
    }

    public function flush(): bool
    {
        return cache()->store($this->store())->tags([$this->prefix()])->flush();
    }

    public function has(string $key): bool
    {
        return cache()->store($this->store())->has($this->key($key));
    }
}
