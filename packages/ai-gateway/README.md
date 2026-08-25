# MTS Laravel AI Gateway

Production AI governance layer for Laravel — prompt management, model routing, cost tracking, rate limiting, caching, audit logging, and tenant quotas on top of the official [Laravel AI SDK](https://github.com/laravel/ai).

## How It Complements Laravel AI SDK

This package **does not replace** the official Laravel AI SDK. It wraps it with enterprise governance features:

| Laravel AI SDK | MTS AI Gateway |
|----------------|----------------|
| Provider abstraction | Prompt template management |
| Agent/tool contracts | Model routing & fallback |
| Text/image/audio generation | Token & cost tracking |
| Streaming & broadcasting | Rate limiting & quotas |
| Structured output | Audit logging & PII masking |
| Middleware for agents | Tenant isolation & security |

Think of it as: **Laravel AI SDK = engine**, **MTS AI Gateway = control panel**.

## Features

- **Prompt Management** - Named templates with versioning, variables, and model binding
- **Model Routing** - Automatic provider selection with allowlists and tier-based routing
- **Provider Fallback** - Automatic failover across providers when one fails
- **Token Tracking** - Every request's input/output/total tokens recorded
- **Cost Estimation** - Per-request cost calculation based on provider pricing
- **Usage Quotas** - Tenant daily tokens, monthly budgets, user daily requests
- **Rate Limiting** - Database-backed per-user and per-tenant rate limits
- **Response Caching** - Cache identical prompts to reduce provider calls
- **Audit Logging** - Full request/response audit trail with PII masking
- **Security** - Model allowlists, tool authorization, tenant isolation
- **Testing** - Fake providers and assertion helpers for tests

## Requirements

- PHP 8.3+
- Laravel 11.x, 12.x, or 13.x

## Installation

```bash
composer require magetech/laravel-ai-gateway
php artisan mts:ai-gateway:install
```

## Configuration

Add your AI provider API keys to `.env`:

```env
MTS_AI_DEFAULT_PROVIDER=openai
MTS_AI_DEFAULT_MODEL=gpt-4o

OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...
GEMINI_API_KEY=...
```

Publish the config:

```bash
php artisan vendor:publish --tag=mts-ai-config
```

## Quick Start

### Define a Prompt

```php
use MageTech\AIGateway\Models\Prompt;

Prompt::create([
    'name' => 'product-description',
    'version' => 1,
    'template' => 'Write a compelling product description for {{ product_name }}. Target audience: {{ audience }}.',
    'variables' => ['product_name', 'audience'],
    'model' => 'gpt-4o',
    'temperature' => 0.7,
]);
```

### Use the Prompt

```php
use MageTech\AIGateway\Support\Facades\AI;

$response = AI::prompt('product-description')
    ->with([
        'product_name' => 'Widget Pro',
        'audience' => 'developers',
    ])
    ->generate();
```

### Override Model/Provider

```php
$response = AI::prompt('product-description')
    ->with(['product_name' => 'Widget Pro', 'audience' => 'developers'])
    ->usingModel('claude-3-5-sonnet-20241022')
    ->usingProvider('anthropic')
    ->withTemperature(0.5)
    ->generate();
```

### Direct Send (Without Template)

```php
$response = AI::send(
    prompt: 'What is Laravel?',
    provider: 'openai',
    model: 'gpt-4o',
    temperature: 0.7,
);
```

## Prompt Versioning

```php
use MageTech\AIGateway\Prompts\PromptManager;

$manager = app(PromptManager::class);

// Create new version
$manager->create([
    'name' => 'product-description',
    'template' => 'Updated template...',
]);

// Get specific version
$template = $manager->get('product-description', version: 2);

// Get latest version
$template = $manager->get('product-description');
```

## Model Routing

Configure model tiers in `config/mts-ai.php`:

```php
'models' => [
    'fast' => ['gpt-4o-mini', 'claude-3-5-haiku-20241022'],
    'balanced' => ['gpt-4o', 'claude-3-5-sonnet-20241022'],
    'premium' => ['gpt-4o', 'claude-opus-4-20250514'],
],
```

## Provider Fallback

If a provider fails, the gateway automatically tries the next provider:

```php
'routing' => [
    'fallback_enabled' => true,
    'max_retries' => 3,
],
```

## Usage Tracking

Every request is automatically tracked in `mts_ai_logs`:

| Field | Description |
|-------|-------------|
| request_id | Unique UUID for each request |
| user_id | Authenticated user |
| tenant_id | Tenant for multi-tenant apps |
| provider | AI provider used |
| model | Model used |
| input_tokens | Input token count |
| output_tokens | Output token count |
| estimated_cost | Estimated cost in USD |
| duration_ms | Request duration |
| status | success/failed/cached |

## Tenant Quotas

```php
'quotas' => [
    'tenant_daily_tokens' => 1000000,
    'tenant_monthly_budget' => 500.00,
    'user_daily_requests' => 500,
],
```

When exceeded, `AiQuotaExceededException` is thrown.

## Testing

```php
use MageTech\AIGateway\Support\Facades\AI;

it('generates product descriptions', function () {
    AI::fake(['content' => 'Amazing product']);

    $result = AI::prompt('product-description')
        ->with(['product_name' => 'Widget'])
        ->generate();

    AI::assertPrompted('product-description');
    AI::assertUsedModel('gpt-4o');
});
```

## Artisan Commands

| Command | Description |
|---------|-------------|
| `mts:ai-gateway:install` | Install and publish config/migrations |
| `mts:ai:make-prompt {name}` | Generate a new prompt class |
| `mts:ai:stats` | Display usage statistics |

## Use Cases

### SaaS Application

```php
AI::prompt('customer-support')
    ->forTenant($tenant->id)
    ->forUser($user->id)
    ->with(['message' => $request->input('message')])
    ->generate();
```

### E-commerce

```php
AI::prompt('product-description')
    ->with(['product' => $product->toArray()])
    ->usingModel('gpt-4o')
    ->generate();
```

### Content Generation

```php
AI::prompt('blog-post')
    ->with(['topic' => $topic, 'tone' => 'professional'])
    ->usingModel('claude-3-5-sonnet-20241022')
    ->withMaxTokens(2000)
    ->generate();
```

### AI Agents with Tool Calling

```php
$response = AI::prompt('agent-task')
    ->with(['task' => 'Analyze sales data'])
    ->withOptions(['tools' => [...]])
    ->generate();
```

## Security

- **Model Allowlists** - Restrict which models can be used
- **Tool Authorization** - Control which tools agents can access
- **Tenant Isolation** - Separate quotas and data per tenant
- **PII Masking** - Automatic masking of sensitive data in audit logs
- **API Key Isolation** - Keys stored in `.env`, never logged

## Testing

```bash
composer test
```

## License

MIT License. See [LICENSE](LICENSE) for details.
