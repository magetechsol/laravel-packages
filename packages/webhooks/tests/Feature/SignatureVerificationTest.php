<?php

declare(strict_types=1);

use MageTech\Webhooks\Inbound\Pipeline\Authenticator;
use MageTech\Webhooks\Inbound\Providers\StripeProvider;
use MageTech\Webhooks\Inbound\Providers\RazorpayProvider;
use MageTech\Webhooks\Inbound\Providers\ShopifyProvider;
use MageTech\Webhooks\Inbound\Providers\GenericProvider;

it('resolves stripe provider', function () {
    $authenticator = new Authenticator();

    $provider = $authenticator->resolveProvider('stripe');

    expect($provider)->toBeInstanceOf(StripeProvider::class);
    expect($provider->name())->toBe('stripe');
});

it('resolves razorpay provider', function () {
    $authenticator = new Authenticator();

    $provider = $authenticator->resolveProvider('razorpay');

    expect($provider)->toBeInstanceOf(RazorpayProvider::class);
    expect($provider->name())->toBe('razorpay');
});

it('resolves shopify provider', function () {
    $authenticator = new Authenticator();

    $provider = $authenticator->resolveProvider('shopify');

    expect($provider)->toBeInstanceOf(ShopifyProvider::class);
    expect($provider->name())->toBe('shopify');
});

it('resolves generic provider as fallback', function () {
    $authenticator = new Authenticator();

    $provider = $authenticator->resolveProvider('unknown');

    expect($provider)->toBeInstanceOf(GenericProvider::class);
    expect($provider->name())->toBe('generic');
});
