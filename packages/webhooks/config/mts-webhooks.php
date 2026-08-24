<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Inbound Webhooks
    |--------------------------------------------------------------------------
    */

    'inbound' => [
        'enabled' => env('MTS_WEBHOOKS_INBOUND_ENABLED', true),

        'route_prefix' => env('MTS_WEBHOOKS_ROUTE_PREFIX', 'webhooks'),

        'middleware' => ['web'],

        'route_middleware' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Configure webhook secrets and signature headers for each supported provider.
    | The 'generic' provider is used as a fallback for unrecognized providers.
    |
    */

    'providers' => [
        'stripe' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'signature_header' => 'Stripe-Signature',
            'timestamp_tolerance' => 300,
        ],

        'razorpay' => [
            'secret' => env('RAZORPAY_WEBHOOK_SECRET'),
            'signature_header' => 'X-Razorpay-Signature',
            'timestamp_tolerance' => 300,
        ],

        'shopify' => [
            'secret' => env('SHOPIFY_WEBHOOK_SECRET'),
            'signature_header' => 'X-Shopify-Hmac-Sha256',
            'timestamp_tolerance' => 300,
        ],

        'magento' => [
            'secret' => env('MAGENTO_WEBHOOK_SECRET'),
            'signature_header' => 'X-Magento-Webhook-Signature',
            'timestamp_tolerance' => 300,
        ],

        'generic' => [
            'secret' => env('MTS_WEBHOOKS_GENERIC_SECRET'),
            'signature_header' => 'X-Webhook-Signature',
            'timestamp_tolerance' => 300,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */

    'security' => [
        'verify_hmac' => env('MTS_WEBHOOKS_VERIFY_HMAC', true),

        'verify_timestamp' => env('MTS_WEBHOOKS_VERIFY_TIMESTAMP', true),

        'timestamp_tolerance' => env('MTS_WEBHOOKS_TIMESTAMP_TOLERANCE', 300),

        'ip_restrictions' => [],

        'mask_sensitive_fields' => [
            'password',
            'token',
            'authorization',
            'secret',
            'card_number',
            'cvv',
            'ssn',
            'api_key',
            'private_key',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Processing
    |--------------------------------------------------------------------------
    */

    'processing' => [
        'queue' => env('MTS_WEBHOOKS_QUEUE', 'default'),

        'connection' => env('MTS_WEBHOOKS_CONNECTION'),

        'timeout' => env('MTS_WEBHOOKS_TIMEOUT', 30),

        /*
         | Map event names to handler classes.
         | Example: 'payment_intent.succeeded' => App\Handlers\PaymentSucceeded::class,
         */
        'handler_map' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry
    |--------------------------------------------------------------------------
    */

    'retry' => [
        'max_attempts' => env('MTS_WEBHOOKS_MAX_ATTEMPTS', 5),

        'base_delay' => env('MTS_WEBHOOKS_BASE_DELAY', 60),

        'max_delay' => env('MTS_WEBHOOKS_MAX_DELAY', 3600),

        'backoff_multiplier' => env('MTS_WEBHOOKS_BACKOFF_MULTIPLIER', 2),

        'retryable_exceptions' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dead Letter
    |--------------------------------------------------------------------------
    */

    'dead_letter' => [
        'enabled' => env('MTS_WEBHOOKS_DEAD_LETTER_ENABLED', true),

        'event' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pruning
    |--------------------------------------------------------------------------
    */

    'pruning' => [
        'enabled' => env('MTS_WEBHOOKS_PRUNING_ENABLED', false),

        'retain_days' => env('MTS_WEBHOOKS_RETAIN_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Outbound Webhooks
    |--------------------------------------------------------------------------
    */

    'outbound' => [
        'timeout' => env('MTS_WEBHOOKS_OUTBOUND_TIMEOUT', 30),

        'queue' => env('MTS_WEBHOOKS_OUTBOUND_QUEUE', 'default'),

        'default_max_attempts' => env('MTS_WEBHOOKS_OUTBOUND_MAX_ATTEMPTS', 5),

        'retry_delay' => env('MTS_WEBHOOKS_OUTBOUND_RETRY_DELAY', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */

    'logging' => [
        'enabled' => env('MTS_WEBHOOKS_LOGGING', true),

        'log_payloads' => env('MTS_WEBHOOKS_LOG_PAYLOADS', false),

        'log_level' => env('MTS_WEBHOOKS_LOG_LEVEL', 'info'),
    ],

];
