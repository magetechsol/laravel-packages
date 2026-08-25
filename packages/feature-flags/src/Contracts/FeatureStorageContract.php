<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Contracts;

interface FeatureStorageContract
{
    public function get(string $key, ?string $environment = null): ?array;

    public function getAll(?string $environment = null): array;

    public function save(string $key, array $data, ?string $environment = null): void;

    public function delete(string $key, ?string $environment = null): void;

    public function clear(): void;
}
