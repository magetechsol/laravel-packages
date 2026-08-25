<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Provider
    |--------------------------------------------------------------------------
    |
    | The default AI provider used when none is explicitly specified.
    | Supported: openai, anthropic, gemini, azure, groq, xai, deepseek,
    |            mistral, ollama, openrouter
    |
    */

    'default' => env('MTS_AI_DEFAULT_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | The default model used when none is explicitly specified in a prompt.
    |
    */

    'default_model' => env('MTS_AI_DEFAULT_MODEL', 'gpt-4o'),

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    |
    | Configure each AI provider. API keys are stored in your .env file.
    | Set a provider's enabled flag to false to exclude it from routing.
    |
    */

    'providers' => [

        'openai' => [
            'enabled' => env('MTS_AI_OPENAI_ENABLED', true),
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_BASE_URL'),
            'organization' => env('OPENAI_ORGANIZATION'),
            'timeout' => (int) env('MTS_AI_OPENAI_TIMEOUT', 120),
        ],

        'anthropic' => [
            'enabled' => env('MTS_AI_ANTHROPIC_ENABLED', true),
            'api_key' => env('ANTHROPIC_API_KEY'),
            'base_url' => env('ANTHROPIC_BASE_URL'),
            'timeout' => (int) env('MTS_AI_ANTHROPIC_TIMEOUT', 120),
        ],

        'gemini' => [
            'enabled' => env('MTS_AI_GEMINI_ENABLED', true),
            'api_key' => env('GEMINI_API_KEY'),
            'base_url' => env('GEMINI_BASE_URL'),
            'timeout' => (int) env('MTS_AI_GEMINI_TIMEOUT', 120),
        ],

        'azure' => [
            'enabled' => env('MTS_AI_AZURE_ENABLED', false),
            'api_key' => env('AZURE_OPENAI_API_KEY'),
            'base_url' => env('AZURE_OPENAI_BASE_URL'),
            'deployment' => env('AZURE_OPENAI_DEPLOYMENT'),
            'api_version' => env('AZURE_OPENAI_API_VERSION', '2024-06-01'),
            'timeout' => (int) env('MTS_AI_AZURE_TIMEOUT', 120),
        ],

        'groq' => [
            'enabled' => env('MTS_AI_GROQ_ENABLED', false),
            'api_key' => env('GROQ_API_KEY'),
            'timeout' => (int) env('MTS_AI_GROQ_TIMEOUT', 60),
        ],

        'xai' => [
            'enabled' => env('MTS_AI_XAI_ENABLED', false),
            'api_key' => env('XAI_API_KEY'),
            'timeout' => (int) env('MTS_AI_XAI_TIMEOUT', 60),
        ],

        'deepseek' => [
            'enabled' => env('MTS_AI_DEEPSEEK_ENABLED', false),
            'api_key' => env('DEEPSEEK_API_KEY'),
            'timeout' => (int) env('MTS_AI_DEEPSEEK_TIMEOUT', 60),
        ],

        'mistral' => [
            'enabled' => env('MTS_AI_MISTRAL_ENABLED', false),
            'api_key' => env('MISTRAL_API_KEY'),
            'timeout' => (int) env('MTS_AI_MISTRAL_TIMEOUT', 60),
        ],

        'ollama' => [
            'enabled' => env('MTS_AI_OLLAMA_ENABLED', false),
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'timeout' => (int) env('MTS_AI_OLLAMA_TIMEOUT', 300),
        ],

        'openrouter' => [
            'enabled' => env('MTS_AI_OPENROUTER_ENABLED', false),
            'api_key' => env('OPENROUTER_API_KEY'),
            'timeout' => (int) env('MTS_AI_OPENROUTER_TIMEOUT', 120),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Model Allowlists
    |--------------------------------------------------------------------------
    |
    | Define model tiers for routing. Models listed here are allowed.
    | The router uses these tiers when selecting models via tier names
    | like 'fast', 'balanced', or 'premium'.
    |
    */

    'models' => [

        'allowlist' => env('MTS_AI_MODEL_ALLOWLIST', true),

        'fast' => [
            'gpt-4o-mini',
            'claude-3-5-haiku-20241022',
            'gemini-2.0-flash',
            'groq-llama-3.1-8b',
        ],

        'balanced' => [
            'gpt-4o',
            'claude-3-5-sonnet-20241022',
            'gemini-2.0-pro',
            'deepseek-chat',
        ],

        'premium' => [
            'gpt-4o',
            'claude-sonnet-4-20250514',
            'claude-opus-4-20250514',
            'gemini-2.5-pro',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Prompt Management
    |--------------------------------------------------------------------------
    |
    | Configure how prompt templates are stored and resolved.
    |
    |   storage: 'database' or 'cache'
    |   cache_ttl: Cache lifetime in minutes when using cache storage
    |
    */

    'prompts' => [

        'storage' => env('MTS_AI_PROMPTS_STORAGE', 'database'),

        'cache_ttl' => (int) env('MTS_AI_PROMPTS_CACHE_TTL', 60),

    ],

    /*
    |--------------------------------------------------------------------------
    | Model Routing
    |--------------------------------------------------------------------------
    |
    | Configure the model router behaviour.
    |
    |   fallback_enabled: Allow automatic provider fallback on failure
    |   max_retries: Maximum retry attempts across fallback providers
    |   strategy: 'round-robin', 'least-cost', or 'latency-based'
    |
    */

    'routing' => [

        'fallback_enabled' => env('MTS_AI_FALLBACK_ENABLED', true),

        'max_retries' => (int) env('MTS_AI_MAX_RETRIES', 3),

        'strategy' => env('MTS_AI_ROUTING_STRATEGY', 'round-robin'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Response Caching
    |--------------------------------------------------------------------------
    |
    | Cache AI responses to avoid redundant provider calls.
    |
    |   enabled: Whether caching is active
    |   driver: Cache store to use (default, redis, database, etc.)
    |   ttl: Cache lifetime in seconds
    |   prefix: Cache key prefix
    |
    */

    'cache' => [

        'enabled' => env('MTS_AI_CACHE_ENABLED', false),

        'driver' => env('MTS_AI_CACHE_DRIVER'),

        'ttl' => (int) env('MTS_AI_CACHE_TTL', 3600),

        'prefix' => env('MTS_AI_CACHE_PREFIX', 'mts_ai'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Database-backed rate limiting for AI requests.
    |
    |   enabled: Whether rate limiting is active
    |   max_requests_per_minute: Per-user request cap
    |   max_tokens_per_minute: Per-user token cap
    |
    */

    'rate_limits' => [

        'enabled' => env('MTS_AI_RATE_LIMIT_ENABLED', true),

        'max_requests_per_minute' => (int) env('MTS_AI_RATE_LIMIT_RPM', 60),

        'max_tokens_per_minute' => (int) env('MTS_AI_RATE_LIMIT_TPM', 100000),

    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant & User Quotas
    |--------------------------------------------------------------------------
    |
    | Enforce usage quotas at the tenant and user level.
    |
    |   tenant_daily_tokens: Maximum tokens per tenant per day
    |   tenant_monthly_budget: Maximum monthly spend per tenant (USD)
    |   user_daily_requests: Maximum requests per user per day
    |
    */

    'quotas' => [

        'enabled' => env('MTS_AI_QUOTAS_ENABLED', true),

        'tenant_daily_tokens' => (int) env('MTS_AI_TENANT_DAILY_TOKENS', 1000000),

        'tenant_monthly_budget' => (float) env('MTS_AI_TENANT_MONTHLY_BUDGET', 500.00),

        'user_daily_requests' => (int) env('MTS_AI_USER_DAILY_REQUESTS', 500),

    ],

    /*
    |--------------------------------------------------------------------------
    | Budget Limits
    |--------------------------------------------------------------------------
    |
    | Global budget limits for the entire application.
    |
    |   daily_limit: Maximum daily spend (USD)
    |   monthly_limit: Maximum monthly spend (USD)
    |   alert_threshold: Percentage of budget to trigger alert (0-100)
    |
    */

    'budgets' => [

        'daily_limit' => (float) env('MTS_AI_BUDGET_DAILY_LIMIT', 1000.00),

        'monthly_limit' => (float) env('MTS_AI_BUDGET_MONTHLY_LIMIT', 10000.00),

        'alert_threshold' => (float) env('MTS_AI_BUDGET_ALERT_THRESHOLD', 80.0),

    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | Record every AI interaction for compliance and debugging.
    |
    |   enabled: Whether audit logging is active
    |   storage: 'database', 'log', or 'both'
    |   mask_pii: Automatically mask personally identifiable information
    |   sensitive_fields: Additional fields to mask beyond defaults
    |
    */

    'audit' => [

        'enabled' => env('MTS_AI_AUDIT_ENABLED', true),

        'storage' => env('MTS_AI_AUDIT_STORAGE', 'database'),

        'mask_pii' => env('MTS_AI_AUDIT_MASK_PII', true),

        'sensitive_fields' => [
            'email',
            'phone',
            'ssn',
            'credit_card',
            'api_key',
            'password',
            'secret',
            'token',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | Security controls for AI operations.
    |
    |   tool_authorization: Require explicit tool permissions
    |   injection_defense: Enable prompt injection detection hooks
    |   redaction_enabled: Enable automatic sensitive data redaction
    |
    */

    'security' => [

        'tool_authorization' => env('MTS_AI_TOOL_AUTHORIZATION', true),

        'injection_defense' => env('MTS_AI_INJECTION_DEFENSE', false),

        'redaction_enabled' => env('MTS_AI_REDACTION_ENABLED', true),

    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Configuration
    |--------------------------------------------------------------------------
    |
    | Pricing per 1M tokens for cost estimation.
    | Update these values as provider pricing changes.
    |
    */

    'costs' => [

        'openai' => [
            'gpt-4o' => ['input' => 2.50, 'output' => 10.00],
            'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
            'o1' => ['input' => 15.00, 'output' => 60.00],
            'o1-mini' => ['input' => 3.00, 'output' => 12.00],
        ],

        'anthropic' => [
            'claude-sonnet-4-20250514' => ['input' => 3.00, 'output' => 15.00],
            'claude-3-5-sonnet-20241022' => ['input' => 3.00, 'output' => 15.00],
            'claude-3-5-haiku-20241022' => ['input' => 0.80, 'output' => 4.00],
            'claude-opus-4-20250514' => ['input' => 15.00, 'output' => 75.00],
        ],

        'gemini' => [
            'gemini-2.0-pro' => ['input' => 1.25, 'output' => 5.00],
            'gemini-2.0-flash' => ['input' => 0.10, 'output' => 0.40],
            'gemini-2.5-pro' => ['input' => 1.25, 'output' => 5.00],
        ],

        'deepseek' => [
            'deepseek-chat' => ['input' => 0.14, 'output' => 0.28],
            'deepseek-reasoner' => ['input' => 0.55, 'output' => 2.19],
        ],

    ],

];
