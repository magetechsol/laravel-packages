<?php

declare(strict_types=1);

namespace MageTech\AIGateway\DTOs;

class ResolvedModel
{
    public function __construct(
        public readonly string $provider,
        public readonly string $model,
        public readonly float $temperature = 0.7,
        public readonly int $maxTokens = 4096,
        public readonly array $fallbacks = [],
    ) {}
}
