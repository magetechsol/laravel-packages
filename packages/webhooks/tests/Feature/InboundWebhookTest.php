<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MageTech\Webhooks\Models\Webhook;

it('receives an inbound webhook and stores it', function () {
    Route::middleware('web')->post('/webhooks/generic', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'generic');
    });

    $payload = [
        'event' => 'order.created',
        'data' => ['id' => 1, 'total' => 100],
    ];

    $response = $this->postJson('/webhooks/generic', $payload);

    $response->assertOk()
        ->assertJson(['status' => 'received']);

    $this->assertDatabaseHas('mts_webhooks', [
        'provider' => 'generic',
        'event' => 'order.created',
        'status' => 'processed',
    ]);
});

it('stores webhook payload and headers', function () {
    Route::middleware('web')->post('/webhooks/stripe', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'stripe');
    });

    $payload = [
        'id' => 'evt_123',
        'type' => 'payment_intent.succeeded',
        'data' => ['amount' => 1000],
    ];

    $response = $this->postJson('/webhooks/stripe', $payload, [
        'Stripe-Signature' => 'test-sig',
    ]);

    $response->assertOk();

    $webhook = Webhook::first();

    expect($webhook->provider)->toBe('stripe');
    expect($webhook->event)->toBe('payment_intent.succeeded');
    expect($webhook->payload)->toHaveKey('id');
    expect($webhook->headers)->toBeArray();
});

it('returns 400 for invalid JSON payload', function () {
    Route::middleware('web')->post('/webhooks/generic', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'generic');
    });

    $response = $this->post('/webhooks/generic', '{invalid json', [
        'Content-Type' => 'application/json',
    ]);

    $response->assertStatus(400);
});

it('handles duplicate webhooks gracefully', function () {
    Route::middleware('web')->post('/webhooks/generic', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'generic');
    });

    $payload = [
        'event' => 'order.created',
        'id' => 'unique_123',
        'data' => ['id' => 1],
    ];

    $this->postJson('/webhooks/generic', $payload)->assertOk();
    $this->postJson('/webhooks/generic', $payload)->assertOk();

    $this->assertDatabaseCount('mts_webhooks', 1);
});
