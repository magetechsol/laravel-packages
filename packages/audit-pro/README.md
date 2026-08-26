# MTS Laravel Audit Pro

**Enterprise Audit Trails & Compliance Logging for Laravel**

[![Latest Stable Version](https://poser.pugx.org/magetech/laravel-audit/v/stable)](https://packagist.org/packages/magetech/laravel-audit)
[![License](https://poser.pugx.org/magetech/laravel-audit/license)](https://packagist.org/packages/magetech/laravel-audit)
[![PHP Version](https://img.shields.io/badge/php-8.2+-blue.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-11.x|12.x-red.svg)](https://laravel.com)

---

## Overview

MTS Laravel Audit Pro is an enterprise-grade audit trail and activity logging package for Laravel applications. It captures important application and business events in a structured, searchable, and secure format.

This package provides audit and compliance-supporting capabilities for applications that require historical traceability of user and system actions.

### Key Features

- **Model Auditing** - Automatic tracking of created, updated, deleted, and restored events
- **Before/After Change Tracking** - Detailed old values, new values, and changed values
- **Manual Audit Recording** - Fluent API for custom business events
- **Actor Resolution** - Automatic identification of who performed actions
- **Request Context** - IP address, user agent, URL, route, and more
- **Multi-Tenancy** - Tenant-aware auditing with automatic tenant resolution
- **Batch Operations** - Group related events with a single batch UUID
- **Hash Chaining** - Tamper-evident records with SHA-256 integrity verification
- **Field Masking** - Configurable masking for sensitive data
- **Field Exclusion** - Exclude sensitive fields from audit logs
- **REST API** - Optional API endpoints for querying audit records
- **Authorization** - Built-in policy with configurable permissions
- **Export** - CSV and JSON export with filtering
- **Retention Policies** - Configurable automatic cleanup
- **Queue Support** - Optional async processing for high-traffic apps
- **Multi-Database** - Store audit records in a separate database

---

## Requirements

- PHP 8.2+
- Laravel 11.x or 12.x
- Database with JSON column support

---

## Installation

### Step 1: Install via Composer

```bash
composer require magetech/laravel-audit
```

### Step 2: Publish Configuration

```bash
php artisan vendor:publish --tag=audit-config
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

### Step 4: Install (Optional)

```bash
php artisan audit:install
```

---

## Configuration

After publishing, review `config/audit.php`:

```php
// Database connection for audit records
'connection' => env('AUDIT_DB_CONNECTION'),

// Enable queue for async processing
'queue' => env('AUDIT_QUEUE', false),

// Fields to exclude from audit logs
'exclude' => [
    'password',
    'password_confirmation',
    'api_token',
    // ...
],

// Enable integrity verification
'integrity' => [
    'enabled' => env('AUDIT_INTEGRITY_ENABLED', false),
    'algorithm' => 'sha256',
],
```

---

## Basic Auditing

### Model Auditing

Add the `Auditable` trait to your model:

```php
use Illuminate\Database\Eloquent\Model;
use MageTech\Audit\Contracts\Auditable;
use MageTech\Audit\Support\AuditableTrait;

class Order extends Model implements Auditable
{
    use AuditableTrait;

    protected $auditExclude = ['internal_notes'];
    protected $auditMasked = ['customer_email'];
}
```

This automatically tracks:
- `created` - When a new record is created
- `updated` - When a record is updated (with old/new values)
- `deleted` - When a record is deleted
- `restored` - When a record is restored

### Querying Audit History

```php
$order = Order::find(1);

// Get all audits for this order
$audits = $order->audits;

// Get the latest change
$latest = $order->getLatestAudit();

// Get the first change
$first = $order->getFirstAudit();
```

---

## Manual Auditing

### Fluent API

```php
use MageTech\Audit\Facades\Audit;

Audit::record()
    ->event('invoice.approved')
    ->on($invoice)
    ->by($user)
    ->withMetadata(['source' => 'admin-panel'])
    ->description('Invoice approved by admin')
    ->save();
```

### Custom Events

```php
Audit::event('payment.refunded')
    ->on($payment)
    ->by($user)
    ->metadata([
        'gateway' => 'stripe',
        'amount' => 50.00,
    ])
    ->save();
```

### Batch Operations

```php
$batchUuid = Audit::beginBatch();

foreach ($items as $item) {
    Audit::record()
        ->event('imported')
        ->on($item)
        ->save();
}

Audit::endBatch();
```

---

## Actor Resolution

The package automatically resolves the actor (user) for each audit event:

```php
// Authenticated user
Audit::record()
    ->event('order.placed')
    ->on($order)
    ->save(); // Actor is automatically resolved

// Manual actor
Audit::record()
    ->event('system.backup')
    ->by([
        'type' => 'system',
        'name' => 'Backup Service',
    ])
    ->save();
```

### Custom Actor Resolver

```php
// app/Services/CustomActorResolver.php
use Illuminate\Http\Request;
use MageTech\Audit\Contracts\ActorResolver;
use MageTech\Audit\Support\ActorData;

class CustomActorResolver implements ActorResolver
{
    public function resolve(Request $request): ?ActorData
    {
        // Custom logic
    }
}
```

Update config:

```php
'actor' => [
    'resolver' => App\Services\CustomActorResolver::class,
],
```

---

## Sensitive Fields

### Exclusion

```php
class User extends Model implements Auditable
{
    use AuditableTrait;

    protected $auditExclude = [
        'password',
        'api_token',
        'secret',
    ];
}
```

### Masking

```php
class User extends Model implements Auditable
{
    use AuditableTrait;

    protected $auditMasked = [
        'email',
        'phone',
    ];
}
```

Custom masking strategies in config:

```php
'masking' => [
    'strategies' => [
        'email' => function ($value) {
            [$name, $domain] = explode('@', $value);
            return substr($name, 0, 1) . str_repeat('*', strlen($name) - 1) . '@' . $domain;
        },
    ],
],
```

---

## Multi-Tenancy

Enable tenant-aware auditing:

```php
'tenancy' => [
    'enabled' => true,
    'resolver' => \App\Services\TenantResolver::class,
],
```

Query by tenant:

```php
Audit::query()
    ->whereTenant(tenant('id'))
    ->latest()
    ->paginate();
```

---

## Querying Audits

```php
use MageTech\Audit\Facades\Audit;

// By event
Audit::query()->whereEvent('updated');

// By actor
Audit::query()->whereActor(User::class, $userId);

// By model
Audit::query()->whereModel(Order::class, $orderId);

// By date range
Audit::query()->whereDateRange('2024-01-01', '2024-12-31');

// By IP
Audit::query()->whereIp('192.168.1.1');

// By tag
Audit::query()->whereTag('important');

// Combined
$audits = Audit::query()
    ->whereEvent('updated')
    ->whereModel(Order::class, $orderId)
    ->latest()
    ->paginate(15);
```

---

## REST API

### Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/audits` | List all audits |
| GET | `/api/audits/{uuid}` | Get single audit |
| GET | `/api/audits/{uuid}/changes` | Get audit changes |
| GET | `/api/auditable/{type}/{id}/audits` | Get audits for model |
| GET | `/api/actors/{id}/audits` | Get audits by actor |
| GET | `/api/audit-stats` | Get audit statistics |

### Query Parameters

```
GET /api/audits?event=updated&model_type=App\Models\Order&from=2024-01-01&per_page=25
```

---

## Authorization

Configure permissions in `config/audit.php`:

```php
'permissions' => [
    'view' => 'audit.view',
    'view_details' => 'audit.view_details',
    'export' => 'audit.export',
    'delete' => 'audit.delete',
    'manage' => 'audit.manage',
],
```

---

## Integrity Verification

Enable hash chaining:

```php
'integrity' => [
    'enabled' => true,
    'algorithm' => 'sha256',
],
```

Verify integrity:

```bash
php artisan audit:verify-integrity
```

---

## Queue Processing

Enable async processing:

```php
'queue' => true,
'queue_connection' => 'redis',
'queue_name' => 'audit',
```

> **Note**: When the queue is unavailable, records are stored synchronously to prevent data loss.

---

## Export

```bash
# CSV export
php artisan audit:export --format=csv --from=2024-01-01 --to=2024-12-31

# JSON export
php artisan audit:export --format=json --event=updated

# Export to specific file
php artisan audit:export --format=csv --output=/path/to/export.csv
```

---

## Retention

```bash
# Preview cleanup
php artisan audit:cleanup --days=365 --dry-run

# Run cleanup
php artisan audit:cleanup --days=365

# Prune by date
php artisan audit:prune --before=2024-01-01
```

---

## Artisan Commands

| Command | Description |
|---------|-------------|
| `audit:install` | Install the package |
| `audit:verify-integrity` | Verify hash chain integrity |
| `audit:export` | Export audit records |
| `audit:cleanup` | Clean up old records |
| `audit:stats` | Display audit statistics |
| `audit:prune` | Prune records by date |

---

## Events

The package dispatches events when audit records are created:

```php
use MageTech\Audit\Events\AuditCreated;
use MageTech\Audit\Events\AuditStored;
use MageTech\Audit\Events\AuditFailed;

Event::listen(AuditCreated::class, function ($event) {
    // Handle audit created
});
```

---

## Performance Tips

1. **Use Queue** - Enable `queue => true` for high-traffic apps
2. **Exclude Fields** - Exclude non-essential fields
3. **Database Connection** - Use a separate database for audit records
4. **Retention** - Enable automatic cleanup
5. **Indexes** - The migration creates optimal indexes by default

---

## Privacy

Configure privacy settings:

```php
'request' => [
    'ip_address' => true,
    'user_agent' => true,
    'url' => true,
    'session_id' => false, // Disable for privacy
],
```

---

## Testing

```bash
# Run all tests
vendor/bin/pest

# Run unit tests
vendor/bin/pest --filter=Unit

# Run feature tests
vendor/bin/pest --filter=Feature
```

---

## Security

- Never logs passwords or authentication secrets
- Configurable sensitive field masking
- Authorization required for viewing audits
- Tenant isolation
- Input validation on all endpoints
- Rate limiting for API endpoints

---

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

---

## Credits

**Developed by [MageTech Solutions](https://www.magetechsol.com/)**

- Website: https://www.magetechsol.com/
- GitHub: https://github.com/magetechsol
