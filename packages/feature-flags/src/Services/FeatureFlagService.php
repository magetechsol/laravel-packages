<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Services;

use Illuminate\Support\Traits\Macroable;
use MageTech\FeatureFlags\Contracts\FeatureCacheContract;
use MageTech\FeatureFlags\Contracts\FeatureEvaluatorContract;
use MageTech\FeatureFlags\Contracts\FeatureRepositoryContract;
use MageTech\FeatureFlags\Models\FeatureFlag;

class FeatureFlagService
{
    use Macroable;

    protected mixed $subject = null;

    public function __construct(
        protected FeatureRepositoryContract $repository,
        protected FeatureEvaluatorContract $evaluator,
        protected FeatureCacheContract $cache,
    ) {}

    public function enabled(string $key, mixed $subject = null): bool
    {
        $flag = $this->resolveFlag($key);

        if ($flag === null) {
            return false;
        }

        return $this->evaluator->isEnabled($flag, $subject ?? $this->subject);
    }

    public function disabled(string $key, mixed $subject = null): bool
    {
        return ! $this->enabled($key, $subject);
    }

    public function active(string $key): bool
    {
        $flag = $this->resolveFlag($key);

        if ($flag === null) {
            return false;
        }

        return $flag->isCurrentlyActive();
    }

    public function for(mixed $subject): static
    {
        $clone = clone $this;
        $clone->subject = $subject;

        return $clone;
    }

    public function variant(string $key, mixed $subject = null): ?string
    {
        $flag = $this->resolveFlag($key);

        if ($flag === null) {
            return null;
        }

        return $this->evaluator->getVariant($flag, $subject ?? $this->subject);
    }

    public function value(string $key, mixed $subject = null): mixed
    {
        $flag = $this->resolveFlag($key);

        if ($flag === null) {
            return null;
        }

        return $this->evaluator->getValue($flag, $subject ?? $this->subject);
    }

    public function config(string $key, mixed $subject = null): mixed
    {
        return $this->value($key, $subject);
    }

    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->all();
    }

    public function create(array $data): FeatureFlag
    {
        return $this->repository->create($data);
    }

    public function update(FeatureFlag $flag, array $data): FeatureFlag
    {
        return $this->repository->update($flag, $data);
    }

    public function delete(FeatureFlag $flag): bool
    {
        return $this->repository->delete($flag);
    }

    public function enable(string $key): FeatureFlag
    {
        $flag = $this->resolveFlag($key);

        if ($flag === null) {
            throw new \MageTech\FeatureFlags\Exceptions\FeatureFlagNotFoundException(
                "Feature flag [{$key}] not found."
            );
        }

        $flag->update(['enabled' => true]);

        $this->repository->clearCache($key);

        return $flag->fresh();
    }

    public function disable(string $key): FeatureFlag
    {
        $flag = $this->resolveFlag($key);

        if ($flag === null) {
            throw new \MageTech\FeatureFlags\Exceptions\FeatureFlagNotFoundException(
                "Feature flag [{$key}] not found."
            );
        }

        $flag->update(['enabled' => false]);

        $this->repository->clearCache($key);

        return $flag->fresh();
    }

    public function clearCache(?string $key = null): void
    {
        $this->repository->clearCache($key);
    }

    protected function resolveFlag(string $key): ?FeatureFlag
    {
        $environment = app(\MageTech\FeatureFlags\Support\EnvironmentResolver::class)->resolve();

        $cacheKey = "flag:{$environment}:{$key}";

        if (config('mts-feature-flags.cache.enabled', true)) {
            $cached = $this->cache->get($cacheKey);

            if ($cached instanceof FeatureFlag) {
                return $cached;
            }
        }

        $flag = $this->repository->findByKey($key, $environment);

        if ($flag !== null && config('mts-feature-flags.cache.enabled', true)) {
            $ttl = config('mts-feature-flags.cache.ttl', 3600);
            $this->cache->put($cacheKey, $flag, $ttl);
        }

        return $flag;
    }
}
