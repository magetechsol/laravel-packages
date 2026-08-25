# User Roles and Permissions

Tenant-scoped user management with role-based access control.

## Default Roles

| Role | Description |
|------|-------------|
| `owner` | Full access, can delete tenant |
| `admin` | Full access except tenant deletion |
| `member` | Standard user access |
| `viewer` | Read-only access |

## Creating Users

```php
use MageTech\SaaS\Models\TenantUser;

$tenantUser = TenantUser::create([
    'tenant_id' => $tenant->id,
    'user_id' => $user->id,
    'role' => 'admin',
    'is_owner' => true,
]);
```

## Checking Roles

```php
if ($tenantUser->isAdmin()) {
    // Full access
}

if ($tenantUser->hasRole('member')) {
    // Has member role
}

if ($tenantUser->isOwner()) {
    // Is the owner
}
```

## Querying by Role

```php
$admins = TenantUser::withRole('admin')->get();
$members = TenantUser::withRole('member')->get();
$tenantUsers = TenantUser::forTenant($tenant->id)->get();
```

## Custom Roles

```php
// Add custom role
$tenantUser = TenantUser::create([
    'tenant_id' => $tenant->id,
    'user_id' => $user->id,
    'role' => 'moderator',
]);

// Check custom role
if ($tenantUser->hasRole('moderator')) {
    // ...
}
```

## Middleware

Apply role checks via middleware in your routes:

```php
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/admin', function () {
        if (!auth()->user()->tenantUser?->isAdmin()) {
            abort(403, 'Unauthorized.');
        }
    });
});
```

## Owner Protection

Only the owner can:
- Delete the tenant
- Transfer ownership
- Change billing

```php
if (!$currentUser->isOwner()) {
    abort(403, 'Only the owner can perform this action.');
}
```
