<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Exceptions;

class AiRateLimitExceededException extends AiGatewayException
{
    public static function requestLimit(int $current, int $limit, ?int $userId = null): static
    {
        $userPart = $userId ? " for user [{$userId}]" : '';

        return new static(
            "Rate limit exceeded{$userPart}. Current: {$current} requests, Limit: {$limit} per minute."
        );
    }

    public static function tokenLimit(int $current, int $limit, ?int $userId = null): static
    {
        $userPart = $userId ? " for user [{$userId}]" : '';

        return new static(
            "Token rate limit exceeded{$userPart}. Current: {$current} tokens, Limit: {$limit} per minute."
        );
    }
}
