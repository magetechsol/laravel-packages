<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Services;

use Illuminate\Database\Eloquent\Collection;
use MageTech\FeatureFlags\Contracts\FeatureCacheContract;
use MageTech\FeatureFlags\Contracts\FeatureRepositoryContract;
use MageTech\FeatureFlags\Events\FeatureCreated;
use MageTech\FeatureFlags\Events\FeatureDeleted;
use MageTech\FeatureFlags\Events\FeatureUpdated;
use MageTech\FeatureFlags\Models\FeatureFlag;

class FeatureRepository implements FeatureRepositoryContract
{
    public function __construct(
        protected FeatureCacheContract $cache,
    ) {}

    public function findByKey(string $key, ?string $environment = null): ?FeatureFlag
    {
        $query = FeatureFlag::where('key', $key);

        if ($environment !== null) {
            $query->where(function ($q) use ($environment) {
                $q->where('environment', $environment)
                    ->orWhereNull('environment');
            });
        }

        return $query->first();
    }

    public function findById(int $id): ?FeatureFlag
    {
        return FeatureFlag::find($id);
    }

    public function all(?string $environment = null): Collection
    {
        $query = FeatureFlag::query();

        if ($environment !== null) {
            $query->forEnvironment($environment);
        }

        return $query->get();
    }

    public function enabled(?string $environment = null): Collection
    {
        $query = FeatureFlag::enabled();

        if ($environment !== null) {
            $query->forEnvironment($environment);
        }

        return $query->get();
    }

    public function create(array $data): FeatureFlag
    {
        if (empty($data['uuid'])) {
            $data['uuid'] = \Illuminate\Support\Str::uuid()->toString();
        }

        $flag = FeatureFlag::create($data);

        $this->clearCache($flag->key);

        if (config('mts-feature-flags.events.dispatch_created', true)) {
            event(new FeatureCreated($flag));
        }

        return $flag;
    }

    public function update(FeatureFlag $flag, array $data): FeatureFlag
    {
        $oldEnabled = $flag->enabled;

        $flag->update($data);

        $this->clearCache($flag->key);

        if (config('mts-feature-flags.events.dispatch_updated', true)) {
            event(new FeatureUpdated($flag, $data));
        }

        if (isset($data['enabled']) && $data['enabled'] !== $oldEnabled) {
            $eventClass = $data['enabled']
                ? \MageTech\FeatureFlags\Events\FeatureEnabled::class
                : \MageTech\FeatureFlags\Events\FeatureDisabled::class;

            if (config($data['enabled']
                ? 'mts-feature-flags.events.dispatch_enabled'
                : 'mts-feature-flags.events.dispatch_disabled', true)) {
                event(new $eventClass($flag));
            }
        }

        return $flag->fresh();
    }

    public function delete(FeatureFlag $flag): bool
    {
        $key = $flag->key;
        $deleted = $flag->delete();

        $this->clearCache($key);

        if (config('mts-feature-flags.events.dispatch_deleted', true)) {
            event(new FeatureDeleted($key));
        }

        return $deleted;
    }

    public function clearCache(?string $key = null): void
    {
        if (! config('mts-feature-flags.cache.enabled', true)) {
            return;
        }

        if ($key !== null) {
            $environment = app(\MageTech\FeatureFlags\Support\EnvironmentResolver::class)->resolve();
            $this->cache->forget("flag:{$environment}:{$key}");
        } else {
            $this->cache->flush();
        }
    }
}
