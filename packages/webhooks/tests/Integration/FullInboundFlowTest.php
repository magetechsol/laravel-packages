<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MageTech\Webhooks\Models\Webhook;
use MageTech\Webhooks\Tests\Fixtures\WebhookTestHandler;

it('processes a complete inbound webhook lifecycle', function () {
    config(['mts-webhooks.processing.handler_map' => [
        'order.created' => WebhookTestHandler::class,
    ]]);

    WebhookTestHandler::reset();

    Route::middleware('web')->post('/webhooks/generic', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'generic');
    });

    $payload = [
        'event' => 'order.created',
        'data' => [
            'id' => 1,
            'total' => 100,
            'customer' => 'John Doe',
        ],
    ];

    $response = $this->postJson('/webhooks/generic', $payload, [
        'X-Webhook-Id' => 'evt_unique_123',
        'X-Webhook-Event' => 'order.created',
    ]);

    $response->assertOk()
        ->assertJson(['status' => 'received']);

    $webhook = Webhook::first();

    expect($webhook)->not->toBeNull();
    expect($webhook->provider)->toBe('generic');
    expect($webhook->event)->toBe('order.created');
    expect($webhook->status->value)->toBe('processed');
    expect($webhook->idempotency_key)->toBe('evt_unique_123');
    expect($webhook->processed_at)->not->toBeNull();

    expect(WebhookTestHandler::$handled)->toHaveCount(1);
    expect(WebhookTestHandler::$handled[0]['event'])->toBe('order.created');
    expect(WebhookTestHandler::$handled[0]['provider'])->toBe('generic');

    $this->assertDatabaseHas('mts_webhook_attempts', [
        'webhook_id' => $webhook->id,
        'status' => 'success',
    ]);
});

it('handles multiple webhook providers in sequence', function () {
    Route::middleware('web')->post('/webhooks/{provider}', function (\Illuminate\Http\Request $request, string $provider) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, $provider);
    });

    $providers = ['stripe', 'razorpay', 'shopify', 'magento', 'generic'];

    foreach ($providers as $provider) {
        $this->postJson("/webhooks/{$provider}", [
            'event' => 'test.event',
            'data' => ['provider' => $provider],
        ])->assertOk();
    }

    $this->assertDatabaseCount('mts_webhooks', 5);

    foreach ($providers as $provider) {
        $this->assertDatabaseHas('mts_webhooks', [
            'provider' => $provider,
            'event' => 'test.event',
        ]);
    }
});
