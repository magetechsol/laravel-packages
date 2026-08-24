# MTS Laravel Webhook Manager

Enterprise-grade inbound and outbound webhook infrastructure for Laravel.

## Requirements

- PHP 8.3+
- Laravel 13.x

## Installation

```bash
composer require magetech/laravel-webhooks
php artisan mts:webhooks:install
php artisan migrate
```

## Quick Start

### Inbound Webhooks

```php
// routes/webhooks.php (auto-published)
use MageTech\Webhooks\Http\Controllers\WebhookController;

Route::post('/{provider}', [WebhookController::class, 'handle']);
```

Register a handler in `config/mts-webhooks.php`:

```php
'handler_map' => [
    'payment_intent.succeeded' => App\Handlers\PaymentSucceeded::class,
    'order.created' => App\Handlers\OrderCreated::class,
    // Provider-specific: 'stripe.payment_intent.succeeded' => ...
],
```

Handler implementation:

```php
use MageTech\Webhooks\Contracts\WebhookHandlerContract;

class PaymentSucceeded implements WebhookHandlerContract
{
    public function handle(array $payload, array $headers, string $event, string $provider): void
    {
        // Process the webhook...
    }
}
```

### Outbound Webhooks

```php
use MageTech\Webhooks\Support\Facades\Webhook;

Webhook::send('order.created')
    ->to('https://partner.example.com/webhook')
    ->payload($order)
    ->signWith($secret)
    ->queue();
```

## Features

### Security

- **HMAC Verification** - SHA-256 signature verification for all providers
- **Timestamp Validation** - Replay attack protection with configurable tolerance
- **IP Restrictions** - Allowlist of authorized IP addresses
- **Sensitive Data Masking** - Automatic masking of passwords, tokens, card numbers

### Provider Support

| Provider | Signature Header | Verification |
|----------|-----------------|--------------|
| Stripe | `Stripe-Signature` | HMAC with timestamp |
| Razorpay | `X-Razorpay-Signature` | HMAC-SHA256 |
| Shopify | `X-Shopify-Hmac-Sha256` | Base64 HMAC |
| Magento | `X-Magento-Webhook-Signature` | HMAC-SHA256 |
| Generic | `X-Webhook-Signature` | HMAC-SHA256 |

### Idempotency

Duplicate events are automatically detected via `idempotency_key`:

```php
// Stripe: uses event ID
// Razorpay: uses entity ID
// Shopify: uses webhook ID
// Custom: use X-Webhook-Id header
```

### Retry Mechanism

- Exponential backoff with configurable base delay, multiplier, and max delay
- Per-provider max attempt limits
- Automatic dead-letter queue after exhausting retries

### Dead Letter Queue

Failed webhooks are moved to dead-letter status after max retries:

```php
// Listen for dead-letter events
Event::listen(WebhookDeadLettered::class, function ($event) {
    // Alert, log, or manually retry...
});
```

## Artisan Commands

| Command | Description |
|---------|-------------|
| `mts:webhooks:install` | Publish config, migrations, routes |
| `mts:webhook:replay {id?}` | Replay failed/dead webhooks |
| `mts:webhook:retry` | Dispatch retry jobs for failed webhooks |
| `mts:webhook:prune` | Remove old webhook records |
| `mts:webhook:stats` | Display webhook statistics |

### Replay

```bash
# Replay a single webhook
php artisan mts:webhook:replay 42

# Replay all failed webhooks from last 7 days
php artisan mts:webhook:replay --from=2026-01-01

# Dry run (preview without processing)
php artisan mts:webhook:replay --dry-run
```

## Configuration

All configuration is in `config/mts-webhooks.php` (publishable).

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `MTS_WEBHOOKS_INBOUND_ENABLED` | `true` | Enable inbound webhook routes |
| `MTS_WEBHOOKS_ROUTE_PREFIX` | `webhooks` | URL prefix for inbound routes |
| `MTS_WEBHOOKS_VERIFY_HMAC` | `true` | Enable HMAC verification |
| `MTS_WEBHOOKS_VERIFY_TIMESTAMP` | `true` | Enable timestamp validation |
| `MTS_WEBHOOKS_TIMESTAMP_TOLERANCE` | `300` | Seconds tolerance for timestamps |
| `MTS_WEBHOOKS_QUEUE` | `default` | Queue name for inbound processing |
| `MTS_WEBHOOKS_MAX_ATTEMPTS` | `5` | Max retry attempts |
| `MTS_WEBHOOKS_BASE_DELAY` | `60` | Base retry delay in seconds |
| `MTS_WEBHOOKS_BACKOFF_MULTIPLIER` | `2` | Exponential backoff multiplier |
| `STRIPE_WEBHOOK_SECRET` | - | Stripe webhook secret |
| `RAZORPAY_WEBHOOK_SECRET` | - | Razorpay webhook secret |
| `SHOPIFY_WEBHOOK_SECRET` | - | Shopify webhook secret |
| `MAGENTO_WEBHOOK_SECRET` | - | Magento webhook secret |

## Database Schema

### `mts_webhooks`

Stores all inbound webhook events with full payload, headers, status tracking, and retry metadata.

### `mts_webhook_attempts`

Records each processing attempt with duration and error information.

### `mts_webhook_deliveries`

Tracks outbound webhook delivery attempts with response codes and retry status.

## Testing

```bash
composer test
composer test-coverage
composer format
composer analyse
```

## Security

- Never log secrets or sensitive data
- Automatic masking of configured sensitive fields
- HMAC signature verification for all providers
- Replay attack protection via timestamp validation
- IP-based access restrictions

## License

MIT License. See [LICENSE](LICENSE) for details.
