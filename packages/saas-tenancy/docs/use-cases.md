# Use Cases

## SaaS Platform

Multi-tenant application where each customer has their own isolated environment.

```php
// Identify tenant from subdomain
// acme.myapp.com → tenant "acme"

$tenant = Tenant::identify();

// All queries are auto-scoped
$users = User::all(); // Only acme's users
```

## White-Label Solution

Resell your platform under different brands.

```php
$tenant = Tenant::create([
    'name' => 'Client Brand',
    'slug' => 'client-brand',
    'domain' => 'clientbrand.com',
    'settings' => [
        'brand_name' => 'Client Brand',
        'logo' => '/logos/client.png',
        'colors' => ['primary' => '#007bff'],
    ],
]);
```

## API Platform

API-based multi-tenancy with token authentication.

```php
Route::middleware(['api', 'tenant.api'])->group(function () {
    Route::get('/data', function () {
        // Auto-scoped to tenant
        return Data::all();
    });
});
```

## Internal Tools

Department or team isolation within an organization.

```php
// Each department is a tenant
$departments = [
    ['name' => 'Engineering', 'slug' => 'engineering'],
    ['name' => 'Marketing', 'slug' => 'marketing'],
    ['name' => 'Sales', 'slug' => 'sales'],
];

foreach ($departments as $dept) {
    Tenant::create($dept);
}
```

## White-Glove Migration

Migrate existing single-tenant apps to multi-tenant.

```php
// 1. Create tenants from existing data
$existingAccounts = Account::all();
foreach ($existingAccounts as $account) {
    Tenant::create([
        'name' => $account->name,
        'slug' => Str::slug($account->name),
        'metadata' => ['migrated_from' => $account->id],
    ]);
}

// 2. Run migrations per tenant
Tenant::all()->each(function ($tenant) {
    Artisan::call('migrate', ['--force' => true]);
});
```
