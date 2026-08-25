<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Rules;

use MageTech\FeatureFlags\Contracts\FeatureRuleContract;
use MageTech\FeatureFlags\Enums\RuleOperator;
use MageTech\FeatureFlags\Enums\RuleType;
use MageTech\FeatureFlags\Models\FeatureFlagRule;

class OrganizationRule implements FeatureRuleContract
{
    public function evaluate(FeatureFlagRule $rule, mixed $subject, RuleOperator $operator): bool
    {
        $orgId = $this->getAttribute($subject, 'organization_id');

        if ($orgId === null) {
            return false;
        }

        return match ($operator) {
            RuleOperator::Equals => (string) $orgId === $rule->value,
            RuleOperator::NotEquals => (string) $orgId !== $rule->value,
            RuleOperator::In => in_array((string) $orgId, explode(',', $rule->value)),
            RuleOperator::NotIn => ! in_array((string) $orgId, explode(',', $rule->value)),
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
        return [RuleType::Organization];
    }
}
