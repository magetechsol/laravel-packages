<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MageTech\ApiToolkit\Middleware\MtsRequestIdMiddleware;
use MageTech\ApiToolkit\Middleware\MtsApiResponseMiddleware;

uses(RefreshDatabase::class);

test('request id middleware adds request id header', function () {
    $middleware = new MtsRequestIdMiddleware();

    $request = Request::create('/test', 'GET');
    $response = new Response();

    $result = $middleware->handle($request, function ($req) use ($response) {
        return $response;
    });

    expect($result->headers->has('X-Request-ID'))->toBeTrue()
        ->and($result->headers->get('X-Request-ID'))->toContain('req_');
});

test('request id middleware preserves existing request id', function () {
    $middleware = new MtsRequestIdMiddleware();

    $request = Request::create('/test', 'GET');
    $request->headers->set('X-Request-ID', 'req_custom123');

    $response = new Response();

    $result = $middleware->handle($request, function ($req) use ($response) {
        return $response;
    });

    expect($result->headers->get('X-Request-ID'))->toBe('req_custom123');
});

test('request id middleware stores request id in attributes', function () {
    $middleware = new MtsRequestIdMiddleware();

    $request = Request::create('/test', 'GET');

    $response = new Response();

    $middleware->handle($request, function ($req) use ($response) {
        return $response;
    });

    expect($request->attributes->has('request_id'))->toBeTrue();
});

test('response middleware adds correlation id header', function () {
    $middleware = new MtsApiResponseMiddleware();

    $request = Request::create('/test', 'GET');
    $response = new Response();

    $result = $middleware->handle($request, function ($req) use ($response) {
        return $response;
    });

    expect($result->headers->has('X-Correlation-ID'))->toBeTrue()
        ->and($result->headers->get('X-Correlation-ID'))->toContain('corr_');
});

test('response middleware adds api version header', function () {
    $middleware = new MtsApiResponseMiddleware();

    $request = Request::create('/test', 'GET');
    $response = new Response();

    $result = $middleware->handle($request, function ($req) use ($response) {
        return $response;
    });

    expect($result->headers->has('X-API-Version'))->toBeTrue()
        ->and($result->headers->get('X-API-Version'))->toBe('v1');
});

test('response middleware preserves existing correlation id', function () {
    $middleware = new MtsApiResponseMiddleware();

    $request = Request::create('/test', 'GET');
    $request->headers->set('X-Correlation-ID', 'corr_custom456');

    $response = new Response();

    $result = $middleware->handle($request, function ($req) use ($response) {
        return $response;
    });

    expect($result->headers->get('X-Correlation-ID'))->toBe('corr_custom456');
});

test('response middleware stores api version in attributes', function () {
    $middleware = new MtsApiResponseMiddleware();

    $request = Request::create('/test', 'GET');

    $response = new Response();

    $middleware->handle($request, function ($req) use ($response) {
        return $response;
    });

    expect($request->attributes->has('api_version'))->toBeTrue()
        ->and($request->attributes->get('api_version'))->toBe('v1');
});
