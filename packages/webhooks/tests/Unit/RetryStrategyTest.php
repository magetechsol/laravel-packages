<?php

declare(strict_types=1);

use MageTech\Webhooks\Support\RetryStrategy;

it('calculates retry delay with exponential backoff', function () {
    $strategy = new RetryStrategy();

    $delay1 = $strategy->calculateDelay(1, 60, 3600, 2.0);
    $delay2 = $strategy->calculateDelay(2, 60, 3600, 2.0);
    $delay3 = $strategy->calculateDelay(3, 60, 3600, 2.0);

    expect($delay1)->toBe(60);
    expect($delay2)->toBe(120);
    expect($delay3)->toBe(240);
});

it('caps delay at max_delay', function () {
    $strategy = new RetryStrategy();

    $delay = $strategy->calculateDelay(10, 60, 3600, 2.0);

    expect($delay)->toBeLessThanOrEqual(3600);
});

it('returns a future carbon instance', function () {
    $strategy = new RetryStrategy();

    $nextRetry = $strategy->calculateNextRetry(1, 60, 3600, 2.0);

    expect($nextRetry->isFuture())->toBeTrue();
});
