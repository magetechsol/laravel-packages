<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Rules;

use MageTech\FeatureFlags\Contracts\FeatureRuleContract;
use MageTech\FeatureFlags\Enums\RuleOperator;
use MageTech\FeatureFlags\Enums\RuleType;
use MageTech\FeatureFlags\Models\FeatureFlagRule;

class AttributeRule implements FeatureRuleContract
{
    public function evaluate(FeatureFlagRule $rule, mixed $subject, RuleOperator $operator): bool
    {
        $actualValue = $this->getAttribute($subject, $rule->attribute);

        if ($actualValue === null) {
            return false;
        }

        $actualStr = (string) $actualValue;
        $ruleStr = $rule->value;

        return match ($operator) {
            RuleOperator::Equals => $actualStr === $ruleStr,
            RuleOperator::NotEquals => $actualStr !== $ruleStr,
            RuleOperator::Contains => str_contains($actualStr, $ruleStr),
            RuleOperator::NotContains => ! str_contains($actualStr, $ruleStr),
            RuleOperator::Starts => str_starts_with($actualStr, $ruleStr),
            RuleOperator::Ends => str_ends_with($actualStr, $ruleStr),
            RuleOperator::GreaterThan => is_numeric($actualValue) && $actualValue > (float) $ruleStr,
            RuleOperator::LessThan => is_numeric($actualValue) && $actualValue < (float) $ruleStr,
            RuleOperator::In => in_array($actualStr, explode(',', $ruleStr)),
            RuleOperator::NotIn => ! in_array($actualStr, explode(',', $ruleStr)),
            RuleOperator::Regex => (bool) preg_match($ruleStr, $actualStr),
            default => false,
        };
    }

    public function getAttribute(mixed $subject, string $attribute): mixed
    {
        if (is_object($subject) && method_exists($subject, 'getAttribute')) {
            return $subject->getAttribute($attribute);
        }

        if (is_object($subject) && property_exists($subject, $attribute)) {
            return $subject->{$attribute};
        }

        if (is_array($subject)) {
            return $subject[$attribute] ?? null;
        }

        return null;
    }

    public function supportedTypes(): array
    {
        return [RuleType::Attribute, RuleType::Country, RuleType::Locale, RuleType::Ip, RuleType::Device, RuleType::Tenant];
    }
}
