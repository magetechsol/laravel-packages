<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Contracts;

use MageTech\FeatureFlags\Enums\RuleOperator;
use MageTech\FeatureFlags\Models\FeatureFlagRule;

interface FeatureRuleContract
{
    public function evaluate(FeatureFlagRule $rule, mixed $subject, RuleOperator $operator): bool;

    public function getAttribute(mixed $subject, string $attribute): mixed;

    public function supportedTypes(): array;
}
