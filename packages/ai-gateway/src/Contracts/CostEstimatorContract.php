<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Contracts;

interface CostEstimatorContract
{
    public function estimate(string $provider, string $model, int $inputTokens, int $outputTokens): float;

    public function getInputPrice(string $provider, string $model): ?float;

    public function getOutputPrice(string $provider, string $model): ?float;
}
