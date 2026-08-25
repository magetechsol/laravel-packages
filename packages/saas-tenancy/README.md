# MTS Laravel SaaS Tenancy

Multi-tenant SaaS infrastructure for Laravel with tenant isolation, user management, and middleware support.

## Features

- **Multi-tenant Architecture** — Shared database or database-per-tenant strategies
- **Tenant Identification** — Subdomain, path, header, session, or cookie resolvers
- **User Management** — Tenant-scoped users with roles (owner, admin, member, viewer)
- **Middleware Stack** — IdentifyTenant, InitializeTenancy, PreventTenantMixing
- **Query Scoping** — Auto-scope Eloquent queries by tenant
- **Activity Logging** — Track tenant events (created, activated, suspended, deleted)
- **Package Tool** — CLI commands for tenant management

## Requirements

- PHP 8.3+
- Laravel 11.x, 12.x, or 13.x
- `magepackages/package-toolkit` (auto-installed)

## Installation

```bash
composer require magepackages/laravel-saas-tenancy
php package-toolkit:install mts-saas
```

## Quick Start

### 1. Run Migrations

```bash
php artisan migrate
```

### 2. Create a Tenant

```bash
php artisan package-toolkit:package mts-saas --create-tenant "Acme Corp"
```

### 3. Identify Tenants

```php
use MageTech\SaaS\Support\Facades\Tenant;

// Identify current tenant
Tenant::identify();

// Get current tenant
$tenant = Tenant::getTenant();

// Create a tenant
$tenant = Tenant::create(['name' => 'Acme', 'slug' => 'acme']);
```

### 4. Use Models

```php
use MageTech\SaaS\Models\Tenant;
use MageTech\SaaS\Models\TenantUser;

// Get tenant users
$users = TenantUser::forTenant($tenant->id)->get();

// Check role
if ($tenantUser->isAdmin()) {
    // ...
}
```

### 5. Scoping Queries

```php
use MageTech\SaaS\Concerns\BelongsToTenant;

class Order extends Model
{
    use BelongsToTenant;
}

// Auto-scoped to current tenant
$orders = Order::all();

// Without scope
$allOrders = Order::withoutGlobalScope(TenantScope::class)->get();
```

## Configuration

Config is published to `config/mts-saas.php`:

```php
return [
    'strategy' => 'shared', // 'shared' or 'database'
    'key_type' => 'uuid',   // 'uuid', 'ulid', or 'int'
    'resolvers' => [
        'subdomain' => ['enabled' => true],
        'path' => ['enabled' => false],
        // ...
    ],
];
```

## Resolvers

| Resolver | Description |
|----------|-------------|
| Subdomain | Extracts tenant from `acme.example.com` |
| Domain | Maps domain to tenant |
| Path | Extracts tenant from URL `/acme/...` |
| Header | Reads `X-Tenant-ID` header |
| Session | Reads tenant from session |
| Cookie | Reads tenant from cookie |

## Events

- `TenantCreated` — Fired when a tenant is created
- `TenantActivated` — Fired when a tenant is activated
- `TenantSuspended` — Fired when a tenant is suspended
- `TenantDeleted` — Fired when a tenant is deleted
- `TenantIdentified` — Fired when a tenant is identified
- `TenantDatabaseReady` — Fired when tenant database is ready

## License

MIT License
