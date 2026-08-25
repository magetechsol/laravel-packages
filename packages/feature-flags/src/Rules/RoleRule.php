<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Rules;

use MageTech\FeatureFlags\Contracts\FeatureRuleContract;
use MageTech\FeatureFlags\Enums\RuleOperator;
use MageTech\FeatureFlags\Enums\RuleType;
use MageTech\FeatureFlags\Models\FeatureFlagRule;

class RoleRule implements FeatureRuleContract
{
    public function evaluate(FeatureFlagRule $rule, mixed $subject, RuleOperator $operator): bool
    {
        $roles = $this->getRoles($subject);

        if (empty($roles)) {
            return false;
        }

        $ruleValue = $rule->value;

        return match ($operator) {
            RuleOperator::Equals => in_array($ruleValue, $roles),
            RuleOperator::NotEquals => ! in_array($ruleValue, $roles),
            RuleOperator::In => count(array_intersect(explode(',', $ruleValue), $roles)) > 0,
            RuleOperator::NotIn => count(array_intersect(explode(',', $ruleValue), $roles)) === 0,
            default => false,
        };
    }

    public function getAttribute(mixed $subject, string $attribute): mixed
    {
        return $this->getRoles($subject);
    }

    public function supportedTypes(): array
    {
        return [RuleType::Role];
    }

    protected function getRoles(mixed $subject): array
    {
        if (is_object($subject) && method_exists($subject, 'getRoleNames')) {
            return $subject->getRoleNames()->toArray();
        }

        if (is_object($subject) && method_exists($subject, 'getAttribute')) {
            $role = $subject->getAttribute('role');

            return $role ? (array) $role : [];
        }

        if (is_array($subject)) {
            return (array) ($subject['role'] ?? ($subject['roles'] ?? []));
        }

        return [];
    }
}
