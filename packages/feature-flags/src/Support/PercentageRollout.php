<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Support;

class PercentageRollout
{
    public function determine(string $key, string $subjectId, int $percentage): bool
    {
        if ($percentage <= 0) {
            return false;
        }

        if ($percentage >= 100) {
            return true;
        }

        $hash = $this->hash($key . ':' . $subjectId);
        $bucket = $this->hashToBucket($hash);

        return $bucket < $percentage;
    }

    public function getVariant(string $key, string $subjectId, array $variants): ?string
    {
        $enabledVariants = array_filter($variants, fn ($v) => $v['enabled'] ?? true);

        if (empty($enabledVariants)) {
            return null;
        }

        $totalWeight = array_sum(array_column($enabledVariants, 'weight'));

        if ($totalWeight <= 0) {
            return null;
        }

        $hash = $this->hash($key . ':variant:' . $subjectId);
        $bucket = $this->hashToBucket($hash);
        $position = 0;

        foreach ($enabledVariants as $variant) {
            $weight = $variant['weight'] ?? 1;
            $position += ($weight / $totalWeight) * 100;

            if ($bucket < $position) {
                return $variant['key'];
            }
        }

        return array_keys($enabledVariants)[0] ?? null;
    }

    protected function hash(string $input): string
    {
        $algo = config('mts-feature-flags.rollout.hash_algo', 'crc32b');

        return hash($algo, $input);
    }

    protected function hashToBucket(string $hash): int
    {
        return hexdec(substr($hash, 0, 8)) % 100;
    }
}
