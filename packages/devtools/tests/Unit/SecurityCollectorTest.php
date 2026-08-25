<?php

declare(strict_types=1);

use MageTech\DevTools\DevTools;

it('returns security data', function () {
    $devtools = app(DevTools::class);
    $data = $devtools->getSecurityData();

    expect($data)->toHaveKeys([
        'debug_mode',
        'environment',
        'configuration',
        'routes',
        'https',
        'php_extensions',
    ]);
});

it('detects debug mode', function () {
    config(['app.debug' => true]);

    $devtools = app(DevTools::class);
    $debug = $devtools->getSecurityData()['debug_mode'];

    expect($debug['enabled'])->toBeTrue()
        ->and($debug['status'])->toBe('ON')
        ->and($debug['risk'])->toBe('high');
});

it('detects environment', function () {
    config(['app.env' => 'production']);

    $devtools = app(DevTools::class);
    $env = $devtools->getSecurityData()['environment'];

    expect($env['value'])->toBe('production')
        ->and($env['is_production'])->toBeTrue();
});

it('counts routes', function () {
    $devtools = app(DevTools::class);
    $routes = $devtools->getSecurityData()['routes'];

    expect($routes['total'])->toBeInt()
        ->and($routes['methods'])->toBeArray();
});

it('returns php extensions', function () {
    $devtools = app(DevTools::class);
    $extensions = $devtools->getSecurityData()['php_extensions'];

    expect($extensions)->toBeArray()
        ->toHaveKey('openssl')
        ->toHaveKey('curl');

    expect($extensions['openssl']['loaded'])->toBeBool()
        ->and($extensions['openssl']['description'])->toBeString();
});
