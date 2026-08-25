<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Routing;

use Illuminate\Config\Repository;
use MageTech\AIGateway\DTOs\ResolvedModel;
use MageTech\AIGateway\Exceptions\AiConfigurationException;
use MageTech\AIGateway\Exceptions\AiModelNotAllowedException;

class ModelRouter
{
    public function __construct(
        protected Repository $config,
    ) {}

    public function resolve(?string $provider = null, ?string $model = null, ?int $tenantId = null): ResolvedModel
    {
        $provider = $provider ?? $this->config->get('mts-ai.default', 'openai');
        $model = $model ?? $this->config->get('mts-ai.default_model', 'gpt-4o');

        $this->validateProvider($provider);
        $this->validateModel($model, $tenantId);

        $providerConfig = $this->config->get("mts-ai.providers.{$provider}", []);

        return new ResolvedModel(
            provider: $provider,
            model: $model,
            temperature: 0.7,
            maxTokens: 4096,
            fallbacks: $this->getFallbacksForModel($model, $tenantId),
        );
    }

    public function getFallbackChain(string $provider, string $model): array
    {
        if (! $this->config->get('mts-ai.routing.fallback_enabled', true)) {
            return [
                ['provider' => $provider, 'model' => $model],
            ];
        }

        $chain = [['provider' => $provider, 'model' => $model]];

        $fallbacks = $this->getFallbacksForModel($model);

        foreach ($fallbacks as $fallback) {
            if ($fallback['provider'] !== $provider || $fallback['model'] !== $model) {
                $chain[] = $fallback;
            }

            if (count($chain) >= $this->config->get('mts-ai.routing.max_retries', 3)) {
                break;
            }
        }

        return $chain;
    }

    public function isAllowed(string $model, ?int $tenantId = null): bool
    {
        if (! $this->config->get('mts-ai.models.allowlist', true)) {
            return true;
        }

        $allModels = array_merge(
            $this->config->get('mts-ai.models.fast', []),
            $this->config->get('mts-ai.models.balanced', []),
            $this->config->get('mts-ai.models.premium', []),
        );

        return in_array($model, $allModels, true);
    }

    public function getModelsForTier(string $tier): array
    {
        return $this->config->get("mts-ai.models.{$tier}", []);
    }

    protected function validateProvider(string $provider): void
    {
        $providerConfig = $this->config->get("mts-ai.providers.{$provider}");

        if (! $providerConfig) {
            throw AiConfigurationException::missingProvider($provider);
        }

        if (! ($providerConfig['enabled'] ?? false)) {
            throw AiModelNotAllowedException::providerNotAllowed($provider);
        }
    }

    protected function validateModel(string $model, ?int $tenantId): void
    {
        if (! $this->isAllowed($model, $tenantId)) {
            throw AiModelNotAllowedException::modelNotAllowed($model, $tenantId);
        }
    }

    protected function getFallbacksForModel(string $model, ?int $tenantId = null): array
    {
        $fallbacks = [];

        $tier = $this->getModelTier($model);

        if ($tier) {
            $models = $this->getModelsForTier($tier);

            foreach ($models as $fallbackModel) {
                if ($fallbackModel !== $model && $this->isAllowed($fallbackModel, $tenantId)) {
                    $fallbacks[] = [
                        'provider' => $this->config->get('mts-ai.default', 'openai'),
                        'model' => $fallbackModel,
                    ];
                }
            }
        }

        $enabledProviders = array_filter(
            $this->config->get('mts-ai.providers', []),
            fn ($config) => $config['enabled'] ?? false
        );

        foreach (array_keys($enabledProviders) as $provider) {
            if ($provider !== $this->config->get('mts-ai.default')) {
                $fallbacks[] = [
                    'provider' => $provider,
                    'model' => $model,
                ];
            }
        }

        return $fallbacks;
    }

    protected function getModelTier(string $model): ?string
    {
        foreach (['fast', 'balanced', 'premium'] as $tier) {
            if (in_array($model, $this->config->get("mts-ai.models.{$tier}", []), true)) {
                return $tier;
            }
        }

        return null;
    }
}
