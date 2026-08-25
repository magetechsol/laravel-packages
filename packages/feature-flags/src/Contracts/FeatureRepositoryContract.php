<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Contracts;

use Illuminate\Database\Eloquent\Collection;
use MageTech\FeatureFlags\Models\FeatureFlag;

interface FeatureRepositoryContract
{
    public function findByKey(string $key, ?string $environment = null): ?FeatureFlag;

    public function findById(int $id): ?FeatureFlag;

    public function all(?string $environment = null): Collection;

    public function enabled(?string $environment = null): Collection;

    public function create(array $data): FeatureFlag;

    public function update(FeatureFlag $flag, array $data): FeatureFlag;

    public function delete(FeatureFlag $flag): bool;

    public function clearCache(?string $key = null): void;
}
