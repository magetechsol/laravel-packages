<?php

declare(strict_types=1);

use MageTech\Webhooks\Models\Webhook;

it('can retry failed webhook', function () {
    $webhook = Webhook::create([
        'provider' => 'generic',
        'event' => 'test.event',
        'payload' => ['test' => true],
        'headers' => [],
        'status' => 'failed',
        'attempts' => 1,
        'max_attempts' => 5,
    ]);

    expect($webhook->canRetry())->toBeTrue();
    expect($webhook->status->value)->toBe('failed');
});

it('tracks attempt count correctly', function () {
    $webhook = Webhook::create([
        'provider' => 'generic',
        'event' => 'test.event',
        'payload' => ['test' => true],
        'headers' => [],
        'status' => 'pending',
        'attempts' => 0,
        'max_attempts' => 3,
    ]);

    $webhook->incrementAttempts();
    expect($webhook->fresh()->attempts)->toBe(1);

    $webhook->incrementAttempts();
    expect($webhook->fresh()->attempts)->toBe(2);
});
