<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Routing;

use Illuminate\Config\Repository;
use MageTech\AIGateway\DTOs\ResolvedModel;

class ProviderResolver
{
    public function __construct(
        protected Repository $config,
    ) {}

    public function resolveChain(string $provider, string $model): array
    {
        $chain = [['provider' => $provider, 'model' => $model]];

        $enabledProviders = array_filter(
            $this->config->get('mts-ai.providers', []),
            fn ($config) => $config['enabled'] ?? false
        );

        foreach (array_keys($enabledProviders) as $altProvider) {
            if ($altProvider !== $provider) {
                $chain[] = ['provider' => $altProvider, 'model' => $model];
            }
        }

        $maxRetries = $this->config->get('mts-ai.routing.max_retries', 3);

        return array_slice($chain, 0, $maxRetries);
    }

    public function isEnabled(string $provider): bool
    {
        return (bool) ($this->config->get("mts-ai.providers.{$provider}.enabled", false));
    }

    public function getEnabledProviders(): array
    {
        $providers = $this->config->get('mts-ai.providers', []);

        return array_filter(
            $providers,
            fn ($config) => $config['enabled'] ?? false
        );
    }
}
