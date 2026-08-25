<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Exceptions;

class AiQuotaExceededException extends AiGatewayException
{
    public static function tenantTokenLimit(int $tenantId, int $dailyTokens, int $limit): static
    {
        return new static(
            "Tenant [{$tenantId}] has exceeded the daily token limit. Used: {$dailyTokens}, Limit: {$limit}."
        );
    }

    public static function tenantBudgetLimit(int $tenantId, float $monthlySpend, float $limit): static
    {
        return new static(
            "Tenant [{$tenantId}] has exceeded the monthly budget limit. Spent: \${$monthlySpend}, Limit: \${$limit}."
        );
    }

    public static function userRequestLimit(int $userId, int $dailyRequests, int $limit): static
    {
        return new static(
            "User [{$userId}] has exceeded the daily request limit. Used: {$dailyRequests}, Limit: {$limit}."
        );
    }

    public static function globalBudgetLimit(string $type, float $current, float $limit): static
    {
        return new static(
            "Global {$type} budget limit exceeded. Current: {$current}, Limit: {$limit}."
        );
    }
}
