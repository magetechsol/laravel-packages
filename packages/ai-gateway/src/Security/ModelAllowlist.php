<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Security;

use Illuminate\Config\Repository;
use MageTech\AIGateway\Exceptions\AiModelNotAllowedException;

class ModelAllowlist
{
    public function __construct(
        protected Repository $config,
    ) {}

    public function isAllowed(string $model, ?int $tenantId = null): bool
    {
        if (! $this->config->get('mts-ai.models.allowlist', true)) {
            return true;
        }

        $allModels = $this->getAllModels();

        if (in_array($model, $allModels, true)) {
            return true;
        }

        if ($tenantId) {
            $tenantModels = $this->config->get("mts-ai.security.tenant_models.{$tenantId}", []);

            return in_array($model, $tenantModels, true);
        }

        return false;
    }

    public function authorize(string $model, ?int $tenantId = null): void
    {
        if (! $this->isAllowed($model, $tenantId)) {
            throw AiModelNotAllowedException::modelNotAllowed($model, $tenantId);
        }
    }

    public function getAllModels(): array
    {
        return array_merge(
            $this->config->get('mts-ai.models.fast', []),
            $this->config->get('mts-ai.models.balanced', []),
            $this->config->get('mts-ai.models.premium', []),
        );
    }

    public function getModelsForTier(string $tier): array
    {
        return $this->config->get("mts-ai.models.{$tier}", []);
    }

    public function getTierForModel(string $model): ?string
    {
        foreach (['fast', 'balanced', 'premium'] as $tier) {
            if (in_array($model, $this->config->get("mts-ai.models.{$tier}", []), true)) {
                return $tier;
            }
        }

        return null;
    }
}
