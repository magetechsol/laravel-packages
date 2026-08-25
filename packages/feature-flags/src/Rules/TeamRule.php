<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Rules;

use MageTech\FeatureFlags\Contracts\FeatureRuleContract;
use MageTech\FeatureFlags\Enums\RuleOperator;
use MageTech\FeatureFlags\Enums\RuleType;
use MageTech\FeatureFlags\Models\FeatureFlagRule;

class TeamRule implements FeatureRuleContract
{
    public function evaluate(FeatureFlagRule $rule, mixed $subject, RuleOperator $operator): bool
    {
        $teamId = $this->getAttribute($subject, 'team_id');

        if ($teamId === null) {
            return false;
        }

        return match ($operator) {
            RuleOperator::Equals => (string) $teamId === $rule->value,
            RuleOperator::NotEquals => (string) $teamId !== $rule->value,
            RuleOperator::In => in_array((string) $teamId, explode(',', $rule->value)),
            RuleOperator::NotIn => ! in_array((string) $teamId, explode(',', $rule->value)),
            default => false,
        };
    }

    public function getAttribute(mixed $subject, string $attribute): mixed
    {
        if (is_object($subject) && method_exists($subject, 'getAttribute')) {
            return $subject->getAttribute($attribute);
        }

        if (is_array($subject)) {
            return $subject[$attribute] ?? null;
        }

        return null;
    }

    public function supportedTypes(): array
    {
        return [RuleType::Team];
    }
}
