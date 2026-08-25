<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Cost;

use MageTech\AIGateway\DTOs\UsageData;
use MageTech\AIGateway\Enums\AiRequestStatus;

class TokenCounter
{
    public function __construct(
        protected CostEstimator $costEstimator,
    ) {}

    public function countFromResponse(mixed $response, string $provider, string $model): ?UsageData
    {
        if (! is_array($response)) {
            return null;
        }

        $usage = $response['usage'] ?? null;

        if (! $usage) {
            return null;
        }

        $inputTokens = $usage['prompt_tokens'] ?? $usage['input_tokens'] ?? 0;
        $outputTokens = $usage['completion_tokens'] ?? $usage['output_tokens'] ?? 0;
        $totalTokens = $usage['total_tokens'] ?? ($inputTokens + $outputTokens);

        $estimatedCost = $this->costEstimator->estimate(
            provider: $provider,
            model: $model,
            inputTokens: $inputTokens,
            outputTokens: $outputTokens,
        );

        return new UsageData(
            requestId: $response['request_id'] ?? '',
            provider: $provider,
            model: $model,
            inputTokens: $inputTokens,
            outputTokens: $outputTokens,
            totalTokens: $totalTokens,
            estimatedCost: $estimatedCost,
            status: AiRequestStatus::Success,
        );
    }

    public function estimateTokens(string $text): int
    {
        $wordCount = str_word_count($text);

        return (int) ceil($wordCount * 1.3);
    }

    public function estimateCost(string $provider, string $model, string $inputText, ?string $outputText = null): float
    {
        $inputTokens = $this->estimateTokens($inputText);
        $outputTokens = $outputText ? $this->estimateTokens($outputText) : 0;

        return $this->costEstimator->estimate($provider, $model, $inputTokens, $outputTokens);
    }
}
