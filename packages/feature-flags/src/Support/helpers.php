<?php

declare(strict_types=1);

if (! function_exists('feature')) {
    /**
     * Check if a feature flag is enabled.
     */
    function feature(string $key, mixed $subject = null): \MageTech\FeatureFlags\Services\FeatureFlagService|bool
    {
        $service = app(\MageTech\FeatureFlags\Services\FeatureFlagService::class);

        if ($subject !== null) {
            return $service->for($subject)->enabled($key);
        }

        return $service;
    }
}

if (! function_exists('feature_enabled')) {
    /**
     * Check if a feature flag is enabled.
     */
    function feature_enabled(string $key, mixed $subject = null): bool
    {
        return app(\MageTech\FeatureFlags\Services\FeatureFlagService::class)
            ->enabled($key, $subject);
    }
}

if (! function_exists('feature_disabled')) {
    /**
     * Check if a feature flag is disabled.
     */
    function feature_disabled(string $key, mixed $subject = null): bool
    {
        return app(\MageTech\FeatureFlags\Services\FeatureFlagService::class)
            ->disabled($key, $subject);
    }
}

if (! function_exists('feature_variant')) {
    /**
     * Get the variant for a feature flag.
     */
    function feature_variant(string $key, mixed $subject = null): ?string
    {
        return app(\MageTech\FeatureFlags\Services\FeatureFlagService::class)
            ->variant($key, $subject);
    }
}

if (! function_exists('feature_value')) {
    /**
     * Get the value for a feature flag.
     */
    function feature_value(string $key, mixed $subject = null): mixed
    {
        return app(\MageTech\FeatureFlags\Services\FeatureFlagService::class)
            ->value($key, $subject);
    }
}

if (! function_exists('feature_config')) {
    /**
     * Get configuration value for a feature flag.
     */
    function feature_config(string $key, mixed $subject = null): mixed
    {
        return app(\MageTech\FeatureFlags\Services\FeatureFlagService::class)
            ->config($key, $subject);
    }
}
