<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MageTech\Webhooks\Models\Webhook;

it('rejects duplicate events with same idempotency key', function () {
    Route::middleware('web')->post('/webhooks/generic', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'generic');
    });

    $payload = [
        'event' => 'order.created',
        'id' => 'unique_event_123',
        'data' => ['id' => 1],
    ];

    $this->postJson('/webhooks/generic', $payload)->assertOk();

    $webhook = Webhook::first();
    expect($webhook->idempotency_key)->toBe('unique_event_123');

    $this->postJson('/webhooks/generic', $payload)->assertOk();

    $this->assertDatabaseCount('mts_webhooks', 1);
});

it('allows events with different idempotency keys', function () {
    Route::middleware('web')->post('/webhooks/generic', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'generic');
    });

    $this->postJson('/webhooks/generic', [
        'event' => 'order.created',
        'id' => 'event_1',
        'data' => ['id' => 1],
    ])->assertOk();

    $this->postJson('/webhooks/generic', [
        'event' => 'order.created',
        'id' => 'event_2',
        'data' => ['id' => 2],
    ])->assertOk();

    $this->assertDatabaseCount('mts_webhooks', 2);
});
