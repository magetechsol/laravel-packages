<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MageTech\Webhooks\Models\Webhook;

it('replays a webhook and creates a new record', function () {
    Route::middleware('web')->post('/webhooks/generic', function (\Illuminate\Http\Request $request) {
        return app(\MageTech\Webhooks\Http\Controllers\WebhookController::class)
            ->handle($request, 'generic');
    });

    $original = Webhook::create([
        'provider' => 'stripe',
        'event' => 'payment.failed',
        'payload' => ['id' => 'pi_failed', 'amount' => 500],
        'headers' => ['Stripe-Signature' => 'test'],
        'status' => 'dead',
        'attempts' => 5,
        'max_attempts' => 5,
    ]);

    $this->artisan('mts:webhook:replay', ['webhook' => $original->id]);

    $this->assertDatabaseCount('mts_webhooks', 2);

    $replayed = Webhook::where('idempotency_key', 'like', 'replay_' . $original->id . '%')->first();

    expect($replayed)->not->toBeNull();
    expect($replayed->provider)->toBe('stripe');
    expect($replayed->event)->toBe('payment.failed');
    expect($replayed->status->value)->toBe('pending');
    expect($replayed->attempts)->toBe(0);
});

it('replays batch of failed webhooks', function () {
    Webhook::create([
        'provider' => 'stripe',
        'event' => 'payment.failed',
        'payload' => ['id' => 'pi_1'],
        'headers' => [],
        'status' => 'failed',
        'attempts' => 2,
        'max_attempts' => 5,
    ]);

    Webhook::create([
        'provider' => 'razorpay',
        'event' => 'payment.failed',
        'payload' => ['id' => 'pay_2'],
        'headers' => [],
        'status' => 'dead',
        'attempts' => 5,
        'max_attempts' => 5,
    ]);

    Webhook::create([
        'provider' => 'stripe',
        'event' => 'payment.succeeded',
        'payload' => ['id' => 'pi_3'],
        'headers' => [],
        'status' => 'processed',
        'attempts' => 1,
        'max_attempts' => 5,
    ]);

    $this->artisan('mts:webhook:replay', ['--provider' => 'stripe']);

    $this->assertDatabaseCount('mts_webhooks', 4);
});
