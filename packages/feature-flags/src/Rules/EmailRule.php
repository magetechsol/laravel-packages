<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Rules;

use MageTech\FeatureFlags\Contracts\FeatureRuleContract;
use MageTech\FeatureFlags\Enums\RuleOperator;
use MageTech\FeatureFlags\Enums\RuleType;
use MageTech\FeatureFlags\Models\FeatureFlagRule;

class EmailRule implements FeatureRuleContract
{
    public function evaluate(FeatureFlagRule $rule, mixed $subject, RuleOperator $operator): bool
    {
        $email = $this->getAttribute($subject, 'email');

        if ($email === null) {
            return false;
        }

        $email = strtolower((string) $email);
        $value = strtolower($rule->value);

        return match ($operator) {
            RuleOperator::Equals => $email === $value,
            RuleOperator::NotEquals => $email !== $value,
            RuleOperator::Contains => str_contains($email, $value),
            RuleOperator::NotContains => ! str_contains($email, $value),
            RuleOperator::Starts => str_starts_with($email, $value),
            RuleOperator::Ends => str_ends_with($email, $value),
            RuleOperator::In => in_array($email, explode(',', $value)),
            RuleOperator::NotIn => ! in_array($email, explode(',', $value)),
            RuleOperator::Regex => (bool) preg_match($rule->value, $email),
            default => false,
        };
    }

    public function getAttribute(mixed $subject, string $attribute): mixed
    {
        if (is_object($subject) && method_exists($subject, 'getAttribute')) {
            return $subject->getAttribute($attribute);
        }

        if (is_object($subject) && isset($subject->email)) {
            return $subject->email;
        }

        if (is_array($subject)) {
            return $subject[$attribute] ?? null;
        }

        return null;
    }

    public function supportedTypes(): array
    {
        return [RuleType::Email];
    }
}
