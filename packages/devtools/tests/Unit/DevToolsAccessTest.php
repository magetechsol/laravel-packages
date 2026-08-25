<?php

declare(strict_types=1);

use MageTech\DevTools\DevTools;

it('respects collector toggle', function () {
    config(['mts-devtools.collectors.application' => false]);

    $devtools = app(DevTools::class);

    expect($devtools->getApplicationData())->toBeEmpty();
});

it('collects all data when enabled', function () {
    $devtools = app(DevTools::class);
    $data = $devtools->getAllData();

    expect($data)->toHaveKeys(['application', 'performance', 'security', 'packages']);
});

it('checks ip authorization', function () {
    $devtools = app(DevTools::class);

    expect($devtools->isAllowedIp('127.0.0.1'))->toBeTrue()
        ->and($devtools->isAllowedIp('::1'))->toBeTrue();
});

it('rejects unauthorized ip', function () {
    config(['mts-devtools.allowed_ips' => ['127.0.0.1']]);

    $devtools = app(DevTools::class);

    expect($devtools->isAllowedIp('192.168.1.100'))->toBeFalse();
});

it('allows all ips when wildcard set', function () {
    config(['mts-devtools.allowed_ips' => ['*']]);

    $devtools = app(DevTools::class);

    expect($devtools->isAllowedIp('192.168.1.100'))->toBeTrue();
});

it('verifies password correctly', function () {
    config(['mts-devtools.password' => 'secret123']);

    $devtools = app(DevTools::class);

    expect($devtools->hasPassword())->toBeTrue()
        ->and($devtools->verifyPassword('secret123'))->toBeTrue()
        ->and($devtools->verifyPassword('wrong'))->toBeFalse();
});

it('has no password when not set', function () {
    config(['mts-devtools.password' => null]);

    $devtools = app(DevTools::class);

    expect($devtools->hasPassword())->toBeFalse();
});
