<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Support;

use Illuminate\Support\Facades\Blade;
use MageTech\FeatureFlags\Services\FeatureFlagService;

class BladeCompiler
{
    public function register(): void
    {
        if (! config('mts-feature-flags.blade.enabled', true)) {
            return;
        }

        Blade::directive('feature', function (string $expression) {
            return "<?php if (app(\\MageTech\\FeatureFlags\\Services\\FeatureFlagService::class)->enabled({$expression})): ?>";
        });

        Blade::directive('endfeature', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('unlessfeature', function (string $expression) {
            return "<?php if (! app(\\MageTech\\FeatureFlags\\Services\\FeatureFlagService::class)->enabled({$expression})): ?>";
        });

        Blade::directive('endunlessfeature', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('featureVariant', function (string $expression) {
            $parts = array_map('trim', explode(',', $expression));

            $key = $parts[0] ?? '';
            $variant = $parts[1] ?? '';

            return "<?php if (app(\\MageTech\\FeatureFlags\\Services\\FeatureFlagService::class)->variant({$key}) === {$variant}): ?>";
        });

        Blade::directive('endfeatureVariant', function () {
            return '<?php endif; ?>';
        });
    }
}
