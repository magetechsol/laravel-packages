<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Contracts;

use MageTech\FeatureFlags\Models\FeatureFlag;

interface FeatureEvaluatorContract
{
    public function isEnabled(FeatureFlag $flag, mixed $subject = null): bool;

    public function isDisabled(FeatureFlag $flag, mixed $subject = null): bool;

    public function getVariant(FeatureFlag $flag, mixed $subject = null): ?string;

    public function getValue(FeatureFlag $flag, mixed $subject = null): mixed;

    public function getConfig(FeatureFlag $flag, mixed $subject = null): mixed;
}
