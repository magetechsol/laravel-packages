<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Support;

use Illuminate\Support\Facades\App;

class EnvironmentResolver
{
    public function resolve(): string
    {
        $configured = config('mts-feature-flags.environment');

        if ($configured !== null) {
            return $configured;
        }

        return App::environment();
    }

    public function matches(string $flagEnvironment): bool
    {
        $current = $this->resolve();

        return $current === $flagEnvironment;
    }
}
