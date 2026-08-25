<?php

declare(strict_types=1);

namespace MageTech\AIGateway\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AiCacheMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('mts-ai.cache.enabled', false)) {
            return $next($request);
        }

        $cacheKey = $this->getCacheKey($request);

        if ($cacheKey) {
            $cached = cache()->store($this->getCacheStore())->get($cacheKey);

            if ($cached !== null) {
                return response()->json([
                    'cached' => true,
                    'data' => $cached,
                ]);
            }
        }

        $response = $next($request);

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300 && $cacheKey) {
            $this->storeCache($cacheKey, $response);
        }

        return $response;
    }

    protected function getCacheKey(Request $request): ?string
    {
        $body = $request->input();

        if (empty($body)) {
            return null;
        }

        $prefix = config('mts-ai.cache.prefix', 'mts_ai');
        $hash = md5(json_encode($body));

        return "{$prefix}:response:{$hash}";
    }

    protected function getCacheStore(): string
    {
        return config('mts-ai.cache.driver') ?? config('cache.default');
    }

    protected function storeCache(string $cacheKey, Response $response): void
    {
        $ttl = config('mts-ai.cache.ttl', 3600);

        $data = json_decode($response->getContent(), true);

        if ($data !== null) {
            cache()->store($this->getCacheStore())->put($cacheKey, $data, $ttl);
        }
    }

    public function flushCache(?string $promptName = null): void
    {
        $prefix = config('mts-ai.cache.prefix', 'mts_ai');

        if ($promptName) {
            cache()->store($this->getCacheStore())->tags(["mts_ai:{$promptName}])->flush();
        } else {
            cache()->store($this->getCacheStore())->tags(['mts_ai'])->flush();
        }
    }
}
