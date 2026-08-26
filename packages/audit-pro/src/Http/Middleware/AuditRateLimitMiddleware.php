<?php

declare(strict_types=1);

namespace MageTech\Audit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AuditRateLimitMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $rateLimit = config('audit.api.rate_limit_per_minute', 60);

        $key = 'audit-api:' . ($request->user()?->getAuthIdentifier() ?? $request->ip());

        if (RateLimiter::tooManyAttempts($key, $rateLimit)) {
            $retryAfter = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $retryAfter,
            ], 429)->withHeaders([
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => $rateLimit,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        RateLimiter::hit($key, 60);

        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $rateLimit,
            'X-RateLimit-Remaining' => RateLimiter::remaining($key, $rateLimit),
        ]);
    }
}
