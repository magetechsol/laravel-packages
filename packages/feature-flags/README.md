# MTS Laravel Feature Flags

Controlled feature rollout and feature management for Laravel.

Developed by [MageTech Solutions](https://www.magetechsol.com/)

## Features

- Boolean feature flags
- Percentage rollouts
- Variant/A/B testing
- Configuration flags
- User targeting (ID, email, role, permission)
- Team & organization targeting
- Environment-specific configuration
- Percentage-based deterministic rollouts
- User-specific overrides with precedence rules
- Scheduling (start/end dates)
- Blade directives
- Middleware
- Artisan commands
- REST API
- Cache support
- Events
- Extensible rule engine

## Requirements

- PHP 8.2+
- Laravel 11.x, 12.x, or 13.x

## Installation

```bash
composer require magetech/laravel-feature-flags
php artisan mts:feature-flags:install
php artisan migrate
```

## Configuration

```php
php artisan vendor:publish --tag=mts-feature-flags-config
```

Published to `config/mts-feature-flags.php`.

## Basic Usage

### Facade

```php
use MageTech\FeatureFlags\Facades\Feature;

if (Feature::enabled('new-dashboard')) {
    // Feature is enabled
}

if (Feature::disabled('legacy-mode')) {
    // Feature is disabled
}

// With user context
$user = auth()->user();
if (Feature::for($user)->enabled('premium-features')) {
    // User has access
}

// Get variant
$variant = Feature::variant('checkout-ui', $user);

// Get value
$value = Feature::value('color-theme', $user);
```

### Helper Functions

```php
if (feature_enabled('new-checkout')) {
    // Feature is enabled
}

$variant = feature_variant('checkout-ui', $user);
$value = feature_value('config-key', $user);
```

### Blade Directives

```blade
@feature('new-dashboard')
    <div>New Dashboard Content</div>
@endfeature

@unlessfeature('legacy-mode')
    <div>Modern interface</div>
@endunlessfeature

@featureVariant('checkout', 'v2')
    <div>Version 2 of checkout</div>
@endfeatureVariant
```

### Middleware

```php
Route::middleware('feature:new-dashboard')->group(function () {
    // Routes only accessible when feature is enabled
});
```

### Artisan Commands

```bash
php artisan mts:feature-flags:list
php artisan mts:feature-flags:create
php artisan mts:feature-flags:enable feature-name
php artisan mts:feature-flags:disable feature-name
php artisan mts:feature-flags:check feature-name
php artisan mts:feature-flags:clear-cache
php artisan mts:feature-flags:export --output=flags.json
php artisan mts:feature-flags:import flags.json
```

### API

Enable in config:

```php
'api' => [
    'enabled' => true,
],
```

Endpoints:

```
GET    /api/feature-flags
GET    /api/feature-flags/{key}
POST   /api/feature-flags
PUT    /api/feature-flags/{key}
DELETE /api/feature-flags/{key}
POST   /api/feature-flags/{key}/enable
POST   /api/feature-flags/{key}/disable
POST   /api/feature-flags/{key}/evaluate
```

## Targeting Rules

### User ID Targeting

```php
$flag->rules()->create([
    'rule_type' => 'user_id',
    'operator' => 'equals',
    'attribute' => 'id',
    'value' => '1001',
]);
```

### Role Targeting

```php
$flag->rules()->create([
    'rule_type' => 'role',
    'operator' => 'equals',
    'attribute' => 'role',
    'value' => 'admin',
]);
```

### Email Targeting

```php
$flag->rules()->create([
    'rule_type' => 'email',
    'operator' => 'ends_with',
    'attribute' => 'email',
    'value' => '@company.com',
]);
```

### Environment Targeting

```php
$flag->rules()->create([
    'rule_type' => 'environment',
    'operator' => 'equals',
    'attribute' => 'environment',
    'value' => 'production',
]);
```

## Percentage Rollouts

```php
$flag = Feature::create([
    'key' => 'new-checkout',
    'name' => 'New Checkout',
    'type' => 'percentage',
    'enabled' => true,
    'rollout_percentage' => 25,
]);
```

The same user will always get the same result (deterministic hashing).

## Variants

```php
$flag = Feature::create([
    'key' => 'checkout-ui',
    'name' => 'Checkout UI',
    'type' => 'variant',
    'enabled' => true,
]);

$flag->variants()->create(['key' => 'control', 'name' => 'Control', 'weight' => 50]);
$flag->variants()->create(['key' => 'treatment', 'name' => 'Treatment', 'weight' => 50]);

$variant = Feature::variant('checkout-ui', $user);
```

## Scheduling

```php
Feature::create([
    'key' => 'holiday-sale',
    'name' => 'Holiday Sale',
    'type' => 'boolean',
    'enabled' => true,
    'starts_at' => '2026-12-01 00:00:00',
    'ends_at' => '2026-12-31 23:59:59',
]);
```

## Environment Configuration

```php
Feature::create([
    'key' => 'new-feature',
    'name' => 'New Feature',
    'type' => 'boolean',
    'enabled' => true,
    'environment' => 'local',
]);

$flag->environments()->create(['environment' => 'production', 'enabled' => false]);
$flag->environments()->create(['environment' => 'staging', 'enabled' => true]);
```

## Overrides

```php
$flag->overrides()->create([
    'subject_type' => 'App\Models\User',
    'subject_id' => 1001,
    'enabled' => true,
    'expires_at' => now()->addDays(7),
]);
```

## Precedence

Feature evaluation follows this precedence (highest to lowest):

1. Explicit user override
2. Targeting rules
3. Percentage rollout
4. Environment setting
5. Global default

## Events

- `FeatureCreated`
- `FeatureUpdated`
- `FeatureDeleted`
- `FeatureEnabled`
- `FeatureDisabled`
- `FeatureEvaluated`
- `FeatureOverrideCreated`
- `FeatureOverrideRemoved`

## Caching

Enabled by default. Configure in `config/mts-feature-flags.php`:

```php
'cache' => [
    'enabled' => true,
    'prefix' => 'mts_feature_flags',
    'ttl' => 3600,
    'store' => null,
],
```

Clear cache: `php artisan mts:feature-flags:clear-cache`

## Extensibility

Implement custom contracts:

```php
use MageTech\FeatureFlags\Contracts\FeatureRuleContract;

class CustomRule implements FeatureRuleContract
{
    public function evaluate($rule, $subject, $operator): bool
    {
        // Custom logic
    }
}
```

Register via service provider:

```php
$ruleEngine = app(MageTech\FeatureFlags\Support\RuleEngine::class);
$ruleEngine->registerRule('custom', new CustomRule());
```

## Testing

```bash
cd packages/feature-flags
composer install
composer test
```

## Security

Report security issues to dev@magetechsolutions.com

## License

MIT License. See [LICENSE](LICENSE) for details.

---

Developed by [MageTech Solutions](https://www.magetechsol.com/)
