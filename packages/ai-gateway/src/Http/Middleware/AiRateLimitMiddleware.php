<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use MageTech\AIGateway\Concerns\GovernanceMiddleware;
use MageTech\AIGateway\Events\AiRateLimited;
use MageTech\AIGateway\Exceptions\AiRateLimitExceededException;
use Symfony\Component\HttpFoundation\Response;

class AiRateLimitMiddleware
{
    use GovernanceMiddleware;

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('mts-ai.rate_limits.enabled', true)) {
            return $next($request);
        }

        $userId = $this->getUserId($request);
        $tenantId = $this->getTenantId($request);

        $this->checkRequestLimit($userId, $tenantId);
        $this->checkTokenLimit($userId, $tenantId);

        $response = $next($request);

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->incrementCounters($userId, $tenantId);
        }

        return $response;
    }

    protected function checkRequestLimit(?int $userId, ?int $tenantId): void
    {
        $limit = config('mts-ai.rate_limits.max_requests_per_minute', 60);

        $key = $userId
            ? "ai_rate_limit:requests:user:{$userId}"
            : "ai_rate_limit:requests:tenant:{$tenantId}";

        $current = RateLimiter::attempts($key);

        if ($current >= $limit) {
            event(new AiRateLimited(
                userId: $userId,
                tenantId: $tenantId,
                limitType: 'requests_per_minute',
                currentCount: $current,
                limit: $limit,
            ));

            throw AiRateLimitExceededException::requestLimit($current, $limit, $userId);
        }
    }

    protected function checkTokenLimit(?int $userId, ?int $tenantId): void
    {
        $limit = config('mts-ai.rate_limits.max_tokens_per_minute', 100000);

        $key = $userId
            ? "ai_rate_limit:tokens:user:{$userId}"
            : "ai_rate_limit:tokens:tenant:{$tenantId}";

        $current = (int) cache()->get($key, 0);

        if ($current >= $limit) {
            event(new AiRateLimited(
                userId: $userId,
                tenantId: $tenantId,
                limitType: 'tokens_per_minute',
                currentCount: $current,
                limit: $limit,
            ));

            throw AiRateLimitExceededException::tokenLimit($current, $limit, $userId);
        }
    }

    protected function incrementCounters(?int $userId, ?int $tenantId): void
    {
        $requestKey = $userId
            ? "ai_rate_limit:requests:user:{$userId}"
            : "ai_rate_limit:requests:tenant:{$tenantId}";

        RateLimiter::hit($requestKey, 60);

        $tokenKey = $userId
            ? "ai_rate_limit:tokens:user:{$userId}"
            : "ai_rate_limit:tokens:tenant:{$tenantId}";

        cache()->increment($tokenKey);
        cache()->put($tokenKey, cache()->get($tokenKey, 0), now()->addMinute());
    }
}
