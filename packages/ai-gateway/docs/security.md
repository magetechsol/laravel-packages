# Security

Security features in the MTS AI Gateway.

## Model Allowlists

Restrict which AI models can be used:

```php
'models' => [
    'allowlist' => true,
    'fast' => ['gpt-4o-mini'],
    'balanced' => ['gpt-4o'],
    'premium' => ['gpt-4o'],
],
```

```php
use MageTech\AIGateway\Security\ModelAllowlist;

$allowlist = app(ModelAllowlist::class);

$allowlist->isAllowed('gpt-4o'); // true
$allowlist->isAllowed('unknown-model'); // false

$allowlist->authorize('gpt-4o'); // passes
$allowlist->authorize('unknown-model'); // throws AiModelNotAllowedException
```

## Tool Authorization

Control which tools AI agents can access:

```php
'use ToolAuthorizer;

$authorizer = app(ToolAuthorizer::class);

$authorizer->isToolAllowed('web_search'); // true
$authorizer->authorizeTool('delete_file'); // throws if not allowed
```

### Configuration

```php
'security' => [
    'tool_authorization' => true,
    'denylisted_tools' => ['delete_file', 'send_email'],
    'allowlisted_tools' => ['web_search', 'read_file'], // null = all allowed
],
```

## Tenant Isolation

Separate data and quotas per tenant:

```php
use MageTech\AIGateway\Support\Facades\AI;

AI::prompt('support')
    ->forTenant($tenant->id)
    ->forUser($user->id)
    ->with(['message' => $input])
    ->generate();
```

All audit logs, usage tracking, and quotas are automatically scoped to the tenant.

## PII Masking

Automatic masking of sensitive data in audit logs:

```php
'audit' => [
    'mask_pii' => true,
    'sensitive_fields' => [
        'email',
        'phone',
        'ssn',
        'credit_card',
        'api_key',
        'password',
    ],
],
```

### Supported Patterns

| Type | Pattern | Masking |
|------|---------|---------|
| Email | `user@domain.com` | `u***r@domain.com` |
| Phone | `555-123-4567` | `*********4567` |
| SSN | `123-45-6789` | `***-**-6789` |
| Credit Card | `4111-1111-1111-1111` | `****-****-****-1111` |
| IP Address | `192.168.1.1` | `192.168.*.*` |

### Custom Patterns

```php
$masker = app(PiiMasker::class);

$masker->addPattern('employee_id', '/\bEMP-\d{4}\b/');
```

## Rate Limiting

Database-backed rate limiting per user/tenant:

```php
'rate_limits' => [
    'enabled' => true,
    'max_requests_per_minute' => 60,
    'max_tokens_per_minute' => 100000,
],
```

## Quotas

Enforce usage limits:

```php
'quotas' => [
    'enabled' => true,
    'tenant_daily_tokens' => 1000000,
    'tenant_monthly_budget' => 500.00,
    'user_daily_requests' => 500,
],
```

## Budget Alerts

Monitor global spending:

```php
'budgets' => [
    'daily_limit' => 1000.00,
    'monthly_limit' => 10000.00,
    'alert_threshold' => 80.0, // Alert at 80% usage
],
```

## Injection Defense

Enable prompt injection detection hooks:

```php
'security' => [
    'injection_defense' => true,
],
```

## API Key Security

- API keys are stored in `.env`, never in config files
- Keys are never logged in audit trails
- Keys are isolated per provider
