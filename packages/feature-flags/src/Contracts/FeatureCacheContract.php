<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Contracts;

interface FeatureCacheContract
{
    public function get(string $key): mixed;

    public function put(string $key, mixed $value, ?int $ttl = null): void;

    public function forget(string $key): void;

    public function flush(): void;

    public function remember(string $key, ?int $ttl, callable $callback): mixed;
}
