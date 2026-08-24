<?php

declare(strict_types=1);

use MageTech\Webhooks\Models\Webhook;

it('marks webhook as failed after max attempts', function () {
    $webhook = Webhook::create([
        'provider' => 'generic',
        'event' => 'test.event',
        'payload' => ['test' => true],
        'headers' => [],
        'status' => 'failed',
        'attempts' => 5,
        'max_attempts' => 5,
    ]);

    expect($webhook->canRetry())->toBeFalse();
});

it('allows retry when under max attempts', function () {
    $webhook = Webhook::create([
        'provider' => 'generic',
        'event' => 'test.event',
        'payload' => ['test' => true],
        'headers' => [],
        'status' => 'failed',
        'attempts' => 2,
        'max_attempts' => 5,
    ]);

    expect($webhook->canRetry())->toBeTrue();
});
