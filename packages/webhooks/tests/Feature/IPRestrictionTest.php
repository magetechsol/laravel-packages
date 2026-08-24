<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('blocks webhook from unauthorized IP', function () {
    config(['mts-webhooks.security.ip_restrictions' => ['10.0.0.1']]);

    Route::middleware('web')->post('/webhooks/generic', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'generic');
    });

    $payload = ['event' => 'test', 'data' => []];

    $response = $this->postJson('/webhooks/generic', $payload);

    $response->assertStatus(403);
});

it('allows webhook from authorized IP', function () {
    config(['mts-webhooks.security.ip_restrictions' => []]);

    Route::middleware('web')->post('/webhooks/generic', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'generic');
    });

    $payload = ['event' => 'test', 'data' => []];

    $response = $this->postJson('/webhooks/generic', $payload);

    $response->assertOk();
});
