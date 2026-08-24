<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MageTech\Webhooks\Models\Webhook;

it('detects replay attacks outside tolerance window', function () {
    config(['mts-webhooks.security.verify_timestamp' => true]);
    config(['mts-webhooks.security.timestamp_tolerance' => 60]);

    Route::middleware('web')->post('/webhooks/generic', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'generic');
    });

    $payload = ['event' => 'test', 'data' => []];

    $response = $this->postJson('/webhooks/generic', $payload, [
        'X-Webhook-Timestamp' => (string) (time() - 700),
    ]);

    $response->assertStatus(409);
});

it('accepts webhooks within tolerance window', function () {
    config(['mts-webhooks.security.verify_timestamp' => true]);
    config(['mts-webhooks.security.timestamp_tolerance' => 300]);

    Route::middleware('web')->post('/webhooks/generic', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'generic');
    });

    $payload = ['event' => 'test', 'data' => []];

    $response = $this->postJson('/webhooks/generic', $payload, [
        'X-Webhook-Timestamp' => (string) time(),
    ]);

    $response->assertOk();
});
