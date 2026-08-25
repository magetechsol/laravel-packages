<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Cost;

use MageTech\AIGateway\Contracts\CostEstimatorContract;

class CostEstimator implements CostEstimatorContract
{
    public function __construct(
        protected \Illuminate\Config\Repository $config,
    ) {}

    public function estimate(string $provider, string $model, int $inputTokens, int $outputTokens): float
    {
        $inputPrice = $this->getInputPrice($provider, $model);
        $outputPrice = $this->getOutputPrice($provider, $model);

        if ($inputPrice === null || $outputPrice === null) {
            return 0.0;
        }

        $inputCost = ($inputTokens / 1_000_000) * $inputPrice;
        $outputCost = ($outputTokens / 1_000_000) * $outputPrice;

        return round($inputCost + $outputCost, 6);
    }

    public function getInputPrice(string $provider, string $model): ?float
    {
        $costs = $this->config->get("mts-ai.costs.{$provider}.{$model}");

        return $costs['input'] ?? null;
    }

    public function getOutputPrice(string $provider, string $model): ?float
    {
        $costs = $this->config->get("mts-ai.costs.{$provider}.{$model}");

        return $costs['output'] ?? null;
    }

    public function getModelsForProvider(string $provider): array
    {
        return array_keys($this->config->get("mts-ai.costs.{$provider}", []));
    }
}
