<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class MtsRequestIdMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = config('mts-api.request_id.header', 'X-Request-ID');
        $requestId = $request->header($header);

        if ($requestId === null || $requestId === '') {
            if (config('mts-api.request_id.generate_if_missing', true)) {
                $requestId = $this->generateRequestId();
            } else {
                $requestId = '';
            }
        }

        $request->attributes->set('request_id', $requestId);

        $response = $next($request);

        if ($requestId !== '') {
            $response->headers->set($header, $requestId);
        }

        return $response;
    }

    /**
     * Generate a unique request ID.
     */
    protected function generateRequestId(): string
    {
        $prefix = config('mts-api.request_id.prefix', 'req_');
        $length = config('mts-api.request_id.length', 32);

        return $prefix . Str::random($length);
    }
}
