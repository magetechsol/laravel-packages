<?php

declare(strict_types=1);

namespace MageTech\ApiToolkit\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class MtsApiResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->addCorrelationId($request);
        $this->addApiVersion($request);

        $response = $next($request);

        $this->addCorrelationIdHeader($request, $response);
        $this->addApiVersionHeader($request, $response);

        return $response;
    }

    /**
     * Add correlation ID to request.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    protected function addCorrelationId(Request $request): void
    {
        $header = config('mts-api.correlation_id.header', 'X-Correlation-ID');
        $correlationId = $request->header($header);

        if ($correlationId === null || $correlationId === '') {
            if (config('mts-api.correlation_id.generate_if_missing', true)) {
                $correlationId = $this->generateCorrelationId();
            } else {
                $correlationId = '';
            }
        }

        $request->attributes->set('correlation_id', $correlationId);
    }

    /**
     * Add API version to request.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    protected function addApiVersion(Request $request): void
    {
        $version = $this->resolveVersion($request);
        $request->attributes->set('api_version', $version);
    }

    /**
     * Add correlation ID header to response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     */
    protected function addCorrelationIdHeader(Request $request, Response $response): void
    {
        $correlationId = $request->attributes->get('correlation_id', '');
        if ($correlationId !== '') {
            $header = config('mts-api.correlation_id.header', 'X-Correlation-ID');
            $response->headers->set($header, $correlationId);
        }
    }

    /**
     * Add API version header to response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     */
    protected function addApiVersionHeader(Request $request, Response $response): void
    {
        if (! config('mts-api.response.include_api_version', true)) {
            return;
        }

        $version = $request->attributes->get('api_version', '');
        if ($version !== '') {
            $header = config('mts-api.versioning.header', 'X-API-Version');
            $response->headers->set($header, $version);
        }
    }

    /**
     * Resolve the API version from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    protected function resolveVersion(Request $request): string
    {
        if (config('mts-api.versioning.enabled', true) === false) {
            return config('mts-api.versioning.default', 'v1');
        }

        $header = config('mts-api.versioning.header', 'X-API-Version');
        $version = $request->header($header);

        if ($version !== null && $version !== '') {
            return $version;
        }

        $route = $request->route();
        if ($route !== null && isset($route->parameters()['version'])) {
            return $route->parameters()['version'];
        }

        if (config('mts-api.versioning.parameter', false)) {
            $version = $request->query('api_version');
            if ($version !== null && $version !== '') {
                return $version;
            }
        }

        return config('mts-api.versioning.default', 'v1');
    }

    /**
     * Generate a unique correlation ID.
     */
    protected function generateCorrelationId(): string
    {
        $prefix = config('mts-api.correlation_id.prefix', 'corr_');

        return $prefix . Str::random(32);
    }
}
