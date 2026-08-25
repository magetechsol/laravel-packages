# Tenant Identification Strategies

Configure how tenants are identified from incoming requests.

## Resolver Types

### Subdomain

Extracts tenant from `acme.example.com`.

```php
'subdomain' => [
    'enabled' => true,
],
```

**Example:** `acme.example.com` → tenant `acme`

### Domain

Maps a full domain to a tenant.

```php
'domain' => [
    'enabled' => true,
],
```

**Example:** `acme.myapp.com` → tenant `acme`

### Path

Extracts tenant from URL path.

```php
'path' => [
    'enabled' => true,
],
```

**Example:** `example.com/acme/...` → tenant `acme`

### Header

Reads tenant from HTTP header.

```php
'header' => [
    'enabled' => true,
    'header_name' => 'X-Tenant-ID',
],
```

### Session

Reads tenant from session.

```php
'session' => [
    'enabled' => true,
    'session_key' => 'tenant_id',
],
```

### Cookie

Reads tenant from cookie.

```php
'cookie' => [
    'enabled' => true,
    'cookie_name' => 'tenant_id',
],
```

## Resolver Order

Resolvers are checked in this order:
1. Subdomain
2. Domain
3. Path
4. Header
5. Session
6. Cookie

## Custom Resolvers

Create a custom resolver by implementing `TenantResolverContract`:

```php
use MageTech\SaaS\Contracts\TenantResolverContract;

class CustomResolver implements TenantResolverContract
{
    public function resolve(\Illuminate\Http\Request $request): ?Tenant
    {
        $value = $request->query('tenant');

        return Tenant::where('slug', $value)->first();
    }

    public function getPriority(): int
    {
        return 100;
    }
}
```

Register in service provider:

```php
$this->app->extend(TenantResolverContract::class, function () {
    return new CustomResolver();
});
```

## Testing

```php
// Test subdomain resolution
$this->get('http://acme.example.com/')
    ->assertOk();

// Test header resolution
$this->get('/', ['X-Tenant-ID' => $tenant->id])
    ->assertOk();
```
