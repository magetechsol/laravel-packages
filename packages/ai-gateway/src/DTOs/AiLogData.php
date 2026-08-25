<?php

declare(strict_types=1);

namespace MageTech\AIGateway\DTOs;

class AiLogData
{
    public function __construct(
        public readonly string $requestId,
        public readonly ?string $correlationId = null,
        public readonly ?int $userId = null,
        public readonly ?int $tenantId = null,
        public readonly ?string $promptName = null,
        public readonly ?int $promptVersion = null,
        public readonly string $provider = '',
        public readonly string $model = '',
        public readonly int $inputTokens = 0,
        public readonly int $outputTokens = 0,
        public readonly int $totalTokens = 0,
        public readonly float $estimatedCost = 0.0,
        public readonly float $durationMs = 0.0,
        public readonly string $status = 'success',
        public readonly ?string $errorMessage = null,
        public readonly array $metadata = [],
        public readonly ?string $ipAddress = null,
    ) {}
}
