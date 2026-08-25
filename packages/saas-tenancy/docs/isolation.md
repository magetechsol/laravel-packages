# Multi-Tenant Isolation

Strategies for isolating tenant data and operations.

## Shared Database Strategy

All tenants share the same database, with a `tenant_id` column on each table.

```php
// config/mts-saas.php
'strategy' => 'shared',
```

**Pros:**
- Simple setup
- Lower cost
- Easy maintenance

**Cons:**
- Must remember to scope all queries
- Potential for data leaks if scoping is missed

## Database-per-Tenant Strategy

Each tenant gets its own database.

```php
// config/mts-saas.php
'strategy' => 'database',
```

**Pros:**
- Complete data isolation
- No risk of cross-tenant queries
- Easy to backup/restore per tenant

**Cons:**
- Higher cost
- More complex migrations
- Slower tenant switching

## Middleware Stack

### IdentifyTenant

Resolves the current tenant from the request.

### InitializeTenancy

Sets up tenant context (database, cache, queue, storage).

### PreventTenantMixing

Throws exception if tenant context leaks between requests.

```php
// app/Http/Kernel.php
'middleware' => [
    \MageTech\SaaS\Http\Middleware\IdentifyTenant::class,
    \MageTech\SaaS\Http\Middleware\InitializeTenancy::class,
    \MageTech\SaaS\Http\Middleware\PreventTenantMixing::class,
],
```

## Isolation Features

| Feature | Shared | Database-per-Tenant |
|---------|--------|---------------------|
| Database | Scoped by `tenant_id` | Separate database |
| Cache | Scoped by tenant ID | Separate store |
| Queue | Scoped by tenant ID | Separate connection |
| Storage | Scoped by tenant path | Separate disk |

## Preventing Data Leaks

1. Always use `BelongsToTenant` trait on tenant models
2. Enable `PreventTenantMixing` middleware in production
3. Use `Tenant::forTenant($id)` for explicit queries
4. Test queries without scope: `Model::withoutGlobalScope(TenantScope::class)`
