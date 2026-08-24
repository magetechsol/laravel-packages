<?php

declare(strict_types=1);

use MageTech\Webhooks\Models\Webhook;

it('marks webhook as dead after exhausting retries', function () {
    $webhook = Webhook::create([
        'provider' => 'generic',
        'event' => 'test.event',
        'payload' => ['test' => true],
        'headers' => [],
        'status' => 'dead',
        'attempts' => 5,
        'max_attempts' => 5,
    ]);

    expect($webhook->status->value)->toBe('dead');
    expect($webhook->dead_at)->not->toBeNull();
    expect($webhook->isTerminal())->toBeTrue();
});
