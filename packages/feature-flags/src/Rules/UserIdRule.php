<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Rules;

use MageTech\FeatureFlags\Contracts\FeatureRuleContract;
use MageTech\FeatureFlags\Enums\RuleOperator;
use MageTech\FeatureFlags\Enums\RuleType;
use MageTech\FeatureFlags\Models\FeatureFlagRule;

class UserIdRule implements FeatureRuleContract
{
    public function evaluate(FeatureFlagRule $rule, mixed $subject, RuleOperator $operator): bool
    {
        $subjectId = $this->getAttribute($subject, 'id');

        if ($subjectId === null) {
            return false;
        }

        return match ($operator) {
            RuleOperator::Equals => (string) $subjectId === $rule->value,
            RuleOperator::NotEquals => (string) $subjectId !== $rule->value,
            RuleOperator::In => in_array((string) $subjectId, explode(',', $rule->value)),
            RuleOperator::NotIn => ! in_array((string) $subjectId, explode(',', $rule->value)),
            default => false,
        };
    }

    public function getAttribute(mixed $subject, string $attribute): mixed
    {
        if (is_numeric($subject)) {
            return (int) $subject;
        }

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
        return [RuleType::UserId];
    }
}
