<?php

declare(strict_types=1);

namespace MageTech\FeatureFlags\Services;

use Illuminate\Support\Facades\App;
use MageTech\FeatureFlags\Contracts\FeatureEvaluatorContract;
use MageTech\FeatureFlags\Events\FeatureEvaluated;
use MageTech\FeatureFlags\Models\FeatureFlag;
use MageTech\FeatureFlags\Models\FeatureFlagOverride;
use MageTech\FeatureFlags\Support\EnvironmentResolver;
use MageTech\FeatureFlags\Support\PercentageRollout;
use MageTech\FeatureFlags\Support\RuleEngine;

class FeatureEvaluator implements FeatureEvaluatorContract
{
    public function __construct(
        protected RuleEngine $ruleEngine,
        protected PercentageRollout $percentageRollout,
        protected EnvironmentResolver $environmentResolver,
    ) {}

    public function isEnabled(FeatureFlag $flag, mixed $subject = null): bool
    {
        $result = $this->evaluate($flag, $subject, 'enabled');

        $this->dispatchEvaluationEvent($flag, $subject, $result);

        return $result;
    }

    public function isDisabled(FeatureFlag $flag, mixed $subject = null): bool
    {
        return ! $this->isEnabled($flag, $subject);
    }

    public function getVariant(FeatureFlag $flag, mixed $subject = null): ?string
    {
        if (! $this->isEnabled($flag, $subject)) {
            return null;
        }

        return $this->resolveVariant($flag, $subject);
    }

    public function getValue(FeatureFlag $flag, mixed $subject = null): mixed
    {
        if (! $this->isEnabled($flag, $subject)) {
            return $flag->default_variant;
        }

        $variantKey = $this->resolveVariant($flag, $subject);

        if ($variantKey === null) {
            return $flag->default_variant;
        }

        $variant = $flag->variants()
            ->where('key', $variantKey)
            ->where('enabled', true)
            ->first();

        return $variant?->value ?? $flag->default_variant;
    }

    public function getConfig(FeatureFlag $flag, mixed $subject = null): mixed
    {
        return $this->getValue($flag, $subject);
    }

    protected function evaluate(FeatureFlag $flag, mixed $subject, string $checkType): bool
    {
        // 1. Check if flag is globally enabled
        if (! $flag->enabled) {
            return false;
        }

        // 2. Check scheduling
        if (! $flag->hasStarted() || $flag->hasEnded()) {
            return false;
        }

        // 3. Check explicit override (highest priority)
        $override = $this->resolveOverride($flag, $subject);

        if ($override !== null) {
            return $override->enabled;
        }

        // 4. Check environment-specific configuration
        $envResult = $this->resolveEnvironment($flag);

        if ($envResult !== null) {
            return $envResult;
        }

        // 5. Check targeting rules
        if ($subject !== null && $flag->rules->count() > 0) {
            $rulesResult = $this->resolveRules($flag, $subject);

            if ($rulesResult !== null) {
                return $rulesResult;
            }
        }

        // 6. Check percentage rollout
        if ($flag->type->value === 'percentage' && $subject !== null) {
            return $this->resolvePercentage($flag, $subject);
        }

        // 7. Default: return the flag's enabled state
        return $flag->enabled;
    }

    protected function resolveOverride(FeatureFlag $flag, mixed $subject): ?FeatureFlagOverride
    {
        if ($subject === null) {
            return null;
        }

        $subjectType = get_class($subject);
        $subjectId = $this->getSubjectId($subject);

        if ($subjectId === null) {
            return null;
        }

        $override = $flag->overrides()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        return $override;
    }

    protected function resolveEnvironment(FeatureFlag $flag): ?bool
    {
        $currentEnvironment = $this->environmentResolver->resolve();

        $envConfig = $flag->environments()
            ->where('environment', $currentEnvironment)
            ->first();

        if ($envConfig === null) {
            return null;
        }

        if (! $envConfig->enabled) {
            return false;
        }

        if ($flag->type->value === 'percentage' && $envConfig->rollout_percentage < 100) {
            return null;
        }

        return true;
    }

    protected function resolveRules(FeatureFlag $flag, mixed $subject): ?bool
    {
        $rules = $flag->rules()
            ->where('enabled', true)
            ->orderBy('priority', 'desc')
            ->get();

        if ($rules->isEmpty()) {
            return null;
        }

        foreach ($rules as $rule) {
            $result = $this->ruleEngine->evaluate($rule, $subject);

            if ($result) {
                return true;
            }
        }

        return false;
    }

    protected function resolvePercentage(FeatureFlag $flag, mixed $subject): bool
    {
        $subjectId = (string) $this->getSubjectId($subject);

        if ($subjectId === '' || $subjectId === '0') {
            return false;
        }

        return $this->percentageRollout->determine(
            $flag->key,
            $subjectId,
            $flag->rollout_percentage
        );
    }

    protected function resolveVariant(FeatureFlag $flag, mixed $subject): ?string
    {
        $variants = $flag->variants()
            ->where('enabled', true)
            ->get()
            ->toArray();

        if (empty($variants)) {
            return $flag->default_variant;
        }

        if ($subject === null) {
            return $variants[0]['key'] ?? $flag->default_variant;
        }

        $subjectId = (string) $this->getSubjectId($subject);

        if ($subjectId === '' || $subjectId === '0') {
            return $variants[0]['key'] ?? $flag->default_variant;
        }

        return $this->percentageRollout->getVariant($flag->key, $subjectId, $variants);
    }

    protected function getSubjectId(mixed $subject): ?int
    {
        if (is_int($subject) || is_numeric($subject)) {
            return (int) $subject;
        }

        if (is_object($subject) && method_exists($subject, 'getKey')) {
            return $subject->getKey();
        }

        return null;
    }

    protected function dispatchEvaluationEvent(FeatureFlag $flag, mixed $subject, bool $result): void
    {
        if (! config('mts-feature-flags.events.dispatch_evaluated', false)) {
            return;
        }

        event(new FeatureEvaluated($flag, $subject, $result));
    }
}
