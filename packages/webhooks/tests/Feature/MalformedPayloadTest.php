<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('rejects empty payload', function () {
    Route::middleware('web')->post('/webhooks/generic', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'generic');
    });

    $response = $this->post('/webhooks/generic', '', [
        'Content-Type' => 'application/json',
    ]);

    $response->assertStatus(400);
});

it('rejects non-JSON payload', function () {
    Route::middleware('web')->post('/webhooks/generic', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'generic');
    });

    $response = $this->post('/webhooks/generic', 'not json', [
        'Content-Type' => 'text/plain',
    ]);

    $response->assertStatus(400);
});

it('rejects JSON array instead of object', function () {
    Route::middleware('web')->post('/webhooks/generic', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'generic');
    });

    $response = $this->postJson('/webhooks/generic', [1, 2, 3]);

    $response->assertStatus(400);
});
