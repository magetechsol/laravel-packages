<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Rules;

use Illuminate\Support\Facades\App;
use MageTech\FeatureFlags\Contracts\FeatureRuleContract;
use MageTech\FeatureFlags\Enums\RuleOperator;
use MageTech\FeatureFlags\Enums\RuleType;
use MageTech\FeatureFlags\Models\FeatureFlagRule;

class EnvironmentRule implements FeatureRuleContract
{
    public function evaluate(FeatureFlagRule $rule, mixed $subject, RuleOperator $operator): bool
    {
        $currentEnvironment = App::environment();

        return match ($operator) {
            RuleOperator::Equals => $currentEnvironment === $rule->value,
            RuleOperator::NotEquals => $currentEnvironment !== $rule->value,
            RuleOperator::In => in_array($currentEnvironment, explode(',', $rule->value)),
            RuleOperator::NotIn => ! in_array($currentEnvironment, explode(',', $rule->value)),
            default => false,
        };
    }

    public function getAttribute(mixed $subject, string $attribute): mixed
    {
        return App::environment();
    }

    public function supportedTypes(): array
    {
        return [RuleType::Environment];
    }
}
