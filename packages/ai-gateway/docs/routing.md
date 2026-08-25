# Model Routing

How the MTS AI Gateway selects providers and handles fallback.

## Routing Flow

```
AI::prompt('template')
    → ModelRouter::resolve()
        → Validate provider (enabled?)
        → Validate model (in allowlist?)
        → Return ResolvedModel
    → ProviderResolver::getFallbackChain()
        → Build ordered list of providers
    → Attempt Provider A
        → Failed? Try Provider B
        → Failed? Try Provider C
    → Return response or throw exception
```

## Model Tiers

Configure model tiers for easy selection:

```php
'models' => [
    'fast' => [
        'gpt-4o-mini',
        'claude-3-5-haiku-20241022',
        'gemini-2.0-flash',
    ],
    'balanced' => [
        'gpt-4o',
        'claude-3-5-sonnet-20241022',
        'gemini-2.0-pro',
    ],
    'premium' => [
        'gpt-4o',
        'claude-opus-4-20250514',
        'gemini-2.5-pro',
    ],
],
```

## Allowlists

Restrict which models can be used:

```php
'models' => [
    'allowlist' => true, // Enable model validation
],
```

When enabled, only models listed in fast/balanced/premium tiers are allowed.

## Provider Fallback

When a provider fails, the gateway tries the next provider in the chain:

```php
'routing' => [
    'fallback_enabled' => true,
    'max_retries' => 3,
],
```

### Fallback Chain Order

1. Same model on different providers
2. Similar model tier on different providers

### Disabling Fallback

```php
'routing' => [
    'fallback_enabled' => false, // Throw immediately on failure
],
```

## Programmatic Routing

```php
use MageTech\AIGateway\Routing\ModelRouter;

$router = app(ModelRouter::class);

// Resolve a model
$resolved = $router->resolve(provider: 'openai', model: 'gpt-4o');

// Check if model is allowed
$router->isAllowed('gpt-4o'); // true

// Get fallback chain
$chain = $router->getFallbackChain('openai', 'gpt-4o');
```

## Events

The router dispatches events for observability:

- `AiRouting` - Before routing decision
- `AiRouted` - After routing decision
- `AiFallbackTriggered` - When fallback occurs
