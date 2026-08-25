<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Contracts;

use MageTech\AIGateway\DTOs\ResolvedModel;

interface ModelRouterContract
{
    public function resolve(?string $provider = null, ?string $model = null, ?int $tenantId = null): ResolvedModel;

    public function getFallbackChain(string $provider, string $model): array;

    public function isAllowed(string $model, ?int $tenantId = null): bool;
}
