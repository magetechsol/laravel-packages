# Usage & Cost Tracking

How the MTS AI Gateway tracks token usage, costs, and provides audit trails.

## Token Tracking

Every request records:

| Field | Description |
|-------|-------------|
| `input_tokens` | Tokens in the prompt |
| `output_tokens` | Tokens in the response |
| `total_tokens` | Sum of input + output |

## Cost Estimation

Costs are estimated per request based on provider pricing:

```php
'costs' => [
    'openai' => [
        'gpt-4o' => ['input' => 2.50, 'output' => 10.00],
        'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
    ],
    'anthropic' => [
        'claude-3-5-sonnet-20241022' => ['input' => 3.00, 'output' => 15.00],
    ],
],
```

Prices are per 1M tokens. Update these as provider pricing changes.

## Programmatic Cost Lookup

```php
use MageTech\AIGateway\Cost\CostEstimator;

$estimator = app(CostEstimator::class);

// Get estimated cost
$cost = $estimator->estimate('openai', 'gpt-4o', 1000, 500);

// Get individual prices
$inputPrice = $estimator->getInputPrice('openai', 'gpt-4o');
$outputPrice = $estimator->getOutputPrice('openai', 'gpt-4o');
```

## Token Counting

```php
use MageTech\AIGateway\Cost\TokenCounter;

$counter = app(TokenCounter::class);

// Estimate tokens from text
$tokens = $counter->estimateTokens('Hello world, this is a test.');

// Estimate cost before making a call
$cost = $counter->estimateCost('openai', 'gpt-4o', $inputText, $outputText);
```

## Audit Logs

Every request is logged to `mts_ai_logs`:

```php
use MageTech\AIGateway\Models\AiLog;

// Query logs
$logs = AiLog::forUser(1)
    ->forToday()
    ->get();

// Get provider breakdown
$stats = AiLog::whereDate('created_at', today())
    ->selectRaw('provider, count(*) as requests, sum(total_tokens) as tokens')
    ->groupBy('provider')
    ->get();
```

## Usage Aggregation

Daily usage is aggregated in `mts_ai_usage`:

```php
use MageTech\AIGateway\Models\AiUsage;

// Get daily tokens for tenant
$tokens = AiUsage::getDailyTokens($tenantId);

// Get monthly spend
$spend = AiUsage::getMonthlySpend($tenantId);

// Get daily requests for user
$requests = AiUsage::getDailyRequests($userId);
```

## PII Masking

Sensitive data in audit logs is automatically masked:

```php
'audit' => [
    'mask_pii' => true,
    'sensitive_fields' => ['email', 'phone', 'ssn', 'credit_card'],
],
```

### Manual Masking

```php
use MageTech\AIGateway\Security\PiiMasker;

$masker = app(PiiMasker::class);

$masked = $masker->mask('Email: john@example.com');
// Result: "Email: j***n@example.com"
```

## Stats Command

```bash
php artisan mts:ai:stats --date=2026-01-15 --provider=openai
```

## Enabling/Disabling

```php
'audit' => [
    'enabled' => true, // Master toggle
    'storage' => 'database', // 'database', 'log', or 'both'
],
```
