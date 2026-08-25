<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Concerns;

use MageTech\AIGateway\DTOs\UsageData;
use MageTech\AIGateway\Models\AiUsage;

trait HasAiTracking
{
    protected function trackUsage(UsageData $usage): void
    {
        AiUsage::record(
            userId: $usage->userId,
            tenantId: $usage->tenantId,
            provider: $usage->provider,
            model: $usage->model,
            inputTokens: $usage->inputTokens,
            outputTokens: $usage->outputTokens,
            estimatedCost: $usage->estimatedCost,
        );
    }
}
