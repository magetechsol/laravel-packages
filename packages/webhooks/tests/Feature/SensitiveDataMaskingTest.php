<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('masks sensitive fields in stored payload', function () {
    Route::middleware('web')->post('/webhooks/generic', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'generic');
    });

    $payload = [
        'event' => 'user.created',
        'data' => [
            'name' => 'John',
            'password' => 'secret123',
            'token' => 'abc-def-ghi',
            'card_number' => '4111111111111111',
        ],
    ];

    $this->postJson('/webhooks/generic', $payload)->assertOk();

    $webhook = \MageTech\Webhooks\Models\Webhook::first();

    expect($webhook->payload['data']['name'])->toBe('John');
    expect($webhook->payload['data']['password'])->not->toBe('secret123');
    expect($webhook->payload['data']['token'])->not->toBe('abc-def-ghi');
    expect($webhook->payload['data']['card_number'])->not->toBe('4111111111111111');
});
