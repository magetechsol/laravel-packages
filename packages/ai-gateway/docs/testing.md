# Testing Guide

How to test AI-powered features with the MTS AI Gateway.

## Fake Provider

The gateway provides a fake mode for testing without calling real AI providers:

```php
use MageTech\AIGateway\Support\Facades\AI;

it('generates content', function () {
    AI::fake(['content' => 'Fake AI response']);

    $result = AI::prompt('greeting')
        ->with(['name' => 'World'])
        ->generate();

    expect($result)->toBe(['content' => 'Fake AI response']);
});
```

## Callable Fakes

Return dynamic responses based on the prompt:

```php
AI::fake(function (string $prompt, ?string $provider, ?string $model) {
    return [
        'content' => "Response to: {$prompt}",
        'model' => $model,
    ];
});
```

## Assertions

```php
AI::fake(['content' => 'response']);

AI::prompt('my-prompt')
    ->with(['key' => 'value'])
    ->usingModel('gpt-4o')
    ->generate();

// Assert a prompt was used
AI::assertPrompted('my-prompt');

// Assert a prompt was NOT used
AI::assertNotPrompted('other-prompt');

// Assert a model was used
AI::assertUsedModel('gpt-4o');

// Assert a provider was used
AI::assertUsedProvider('openai');

// Assert token usage
AI::assertTokens(100, 1000);
```

## Restore Fakes

```php
AI::fake(['content' => 'fake']);
AI::restore();
```

## Test Case Setup

```php
use MageTech\AIGateway\Tests\TestCase;

class FeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Audit logging is disabled by default in tests
        // Migrations are auto-loaded
    }
}
```

## Testing Prompts

```php
use MageTech\AIGateway\Models\Prompt;

it('uses the correct prompt template', function () {
    Prompt::create([
        'name' => 'test-prompt',
        'version' => 1,
        'template' => 'Hello {{ name }}',
        'status' => 'active',
    ]);

    AI::fake(['content' => 'Hi']);

    AI::prompt('test-prompt')
        ->with(['name' => 'World'])
        ->generate();

    AI::assertPrompted('test-prompt');
});
```

## Testing Quotas

```php
it('throws when quota exceeded', function () {
    config(['mts-ai.quotas.user_daily_requests' => 1]);

    // First request passes
    AiUsage::record(1, null, 'openai', 'gpt-4o', 100, 50, 0.001);

    // Check quota
    $requests = AiUsage::getDailyRequests(1);
    expect($requests)->toBe(1);
});
```

## Testing Rate Limits

```php
it('enforces rate limits', function () {
    config(['mts-ai.rate_limits.enabled' => true]);
    config(['mts-ai.rate_limits.max_requests_per_minute' => 2]);

    // Simulate rate limiting
    $key = 'ai_rate_limit:requests:user:1';
    RateLimiter::hit($key, 60);
    RateLimiter::hit($key, 60);

    expect(RateLimiter::attempts($key))->toBe(2);
});
```

## Integration Tests

```php
it('tracks usage end-to-end', function () {
    Prompt::create([
        'name' => 'tracked-prompt',
        'version' => 1,
        'template' => 'Hello',
        'status' => 'active',
    ]);

    AI::fake(['content' => 'Hi']);

    AI::prompt('tracked-prompt')
        ->with([])
        ->generate();

    AI::assertPrompted('tracked-prompt');
    AI::assertUsedModel('gpt-4o');
});
```
