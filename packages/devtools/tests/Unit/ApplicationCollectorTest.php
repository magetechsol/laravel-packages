<?php

declare(strict_types=1);

use MageTech\DevTools\DevTools;

it('returns enabled status from config', function () {
    $devtools = app(DevTools::class);

    expect($devtools->isEnabled())->toBeTrue();
});

it('can disable devtools', function () {
    config(['mts-devtools.enabled' => false]);

    $devtools = app(DevTools::class);

    expect($devtools->isEnabled())->toBeFalse();
});

it('returns application data', function () {
    $devtools = app(DevTools::class);
    $data = $devtools->getApplicationData();

    expect($data)->toHaveKeys([
        'laravel',
        'php',
        'environment',
        'database',
        'cache',
        'queue',
    ]);
});

it('returns laravel version', function () {
    $devtools = app(DevTools::class);

    expect($devtools->getApplicationData()['laravel'])->toBeString();
});

it('returns php version', function () {
    $devtools = app(DevTools::class);

    expect($devtools->getApplicationData()['php'])->toBe(PHP_VERSION);
});

it('returns environment name', function () {
    config(['app.env' => 'testing']);

    $devtools = app(DevTools::class);

    expect($devtools->getApplicationData()['environment'])->toBe('testing');
});

it('returns database info', function () {
    $devtools = app(DevTools::class);
    $db = $devtools->getApplicationData()['database'];

    expect($db)->toHaveKeys(['driver', 'host', 'database', 'version']);
});

it('returns cache config', function () {
    $devtools = app(DevTools::class);
    $cache = $devtools->getApplicationData()['cache'];

    expect($cache)->toHaveKeys(['default', 'stores']);
});

it('returns queue config', function () {
    $devtools = app(DevTools::class);
    $queue = $devtools->getApplicationData()['queue'];

    expect($queue)->toHaveKeys(['default', 'connections']);
});
