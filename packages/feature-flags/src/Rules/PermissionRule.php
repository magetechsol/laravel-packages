<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Rules;

use MageTech\FeatureFlags\Contracts\FeatureRuleContract;
use MageTech\FeatureFlags\Enums\RuleOperator;
use MageTech\FeatureFlags\Enums\RuleType;
use MageTech\FeatureFlags\Models\FeatureFlagRule;

class PermissionRule implements FeatureRuleContract
{
    public function evaluate(FeatureFlagRule $rule, mixed $subject, RuleOperator $operator): bool
    {
        $permissions = $this->getPermissions($subject);

        if (empty($permissions)) {
            return false;
        }

        $ruleValue = $rule->value;

        return match ($operator) {
            RuleOperator::Equals => in_array($ruleValue, $permissions),
            RuleOperator::NotEquals => ! in_array($ruleValue, $permissions),
            RuleOperator::In => count(array_intersect(explode(',', $ruleValue), $permissions)) > 0,
            RuleOperator::NotIn => count(array_intersect(explode(',', $ruleValue), $permissions)) === 0,
            default => false,
        };
    }

    public function getAttribute(mixed $subject, string $attribute): mixed
    {
        return $this->getPermissions($subject);
    }

    public function supportedTypes(): array
    {
        return [RuleType::Permission];
    }

    protected function getPermissions(mixed $subject): array
    {
        if (is_object($subject) && method_exists($subject, 'getAllPermissions')) {
            return $subject->getAllPermissions()->pluck('name')->toArray();
        }

        if (is_object($subject) && method_exists($subject, 'hasPermissionTo')) {
            return $subject->getAllPermissions()->pluck('name')->toArray();
        }

        if (is_array($subject)) {
            return (array) ($subject['permissions'] ?? []);
        }

        return [];
    }
}
