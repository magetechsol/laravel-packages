<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Services;

use Illuminate\Support\Facades\Cache;
use MageTech\FeatureFlags\Contracts\FeatureCacheContract;

class FeatureCache implements FeatureCacheContract
{
    protected string $prefix;

    public function __construct()
    {
        $this->prefix = config('mts-feature-flags.cache.prefix', 'mts_feature_flags');
    }

    public function get(string $key): mixed
    {
        return Cache::store($this->getStore())->get($this->prefix . ':' . $key);
    }

    public function put(string $key, mixed $value, ?int $ttl = null): void
    {
        $ttl = $ttl ?? config('mts-feature-flags.cache.ttl', 3600);

        Cache::store($this->getStore())->put(
            $this->prefix . ':' . $key,
            $value,
            $ttl
        );
    }

    public function forget(string $key): void
    {
        Cache::store($this->getStore())->forget($this->prefix . ':' . $key);
    }

    public function flush(): void
    {
        Cache::store($this->getStore())->tags([$this->prefix])->flush();
    }

    public function remember(string $key, ?int $ttl, callable $callback): mixed
    {
        $ttl = $ttl ?? config('mts-feature-flags.cache.ttl', 3600);

        return Cache::store($this->getStore())->remember(
            $this->prefix . ':' . $key,
            $ttl,
            $callback
        );
    }

    protected function getStore(): string
    {
        return config('mts-feature-flags.cache.store') ?? Cache::getDefaultDriver();
    }
}
