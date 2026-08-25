<?php

declare(strict_types=1);

use MageTech\DevTools\DevTools;
use MageTech\DevTools\Enums\HealthStatus;

it('returns health status array', function () {
    $devtools = app(DevTools::class);
    $health = $devtools->getHealthStatus();

    expect($health)->toBeArray()
        ->toHaveKeys(['environment', 'debug_mode', 'database', 'slow_queries', 'failed_jobs']);
});

it('each health check has required structure', function () {
    $devtools = app(DevTools::class);
    $health = $devtools->getHealthStatus();

    foreach ($health as $check) {
        expect($check)->toHaveKeys(['label', 'status', 'message']);
        expect($check['status'])->toBeInstanceOf(HealthStatus::class);
    }
});

it('returns overall health status', function () {
    $devtools = app(DevTools::class);

    expect($devtools->getOverallHealth())->toBeInstanceOf(HealthStatus::class);
});

it('returns critical health when debug mode is on', function () {
    config(['app.debug' => true]);

    $devtools = app(DevTools::class);
    $health = $devtools->getHealthStatus();

    expect($health['debug_mode']['status'])->toBe(HealthStatus::Critical);
});

it('returns healthy when debug mode is off', function () {
    config(['app.debug' => false]);

    $devtools = app(DevTools::class);
    $health = $devtools->getHealthStatus();

    expect($health['debug_mode']['status'])->toBe(HealthStatus::Healthy);
});

it('warns when slow queries are detected', function () {
    config(['mts-devtools.slow_query_threshold' => 0]);

    $devtools = app(DevTools::class);
    $health = $devtools->getHealthStatus();

    expect($health['slow_queries']['status'])->not->toBe(HealthStatus::Unknown);
});
