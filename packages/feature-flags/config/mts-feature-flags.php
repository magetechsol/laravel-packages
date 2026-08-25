<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Environment
    |--------------------------------------------------------------------------
    |
    | The default environment used for feature flag evaluation.
    | Set to null to use the application's current environment.
    |
    */

    'environment' => env('MTS_FEATURE_FLAGS_ENVIRONMENT'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Control how feature flags are cached. Disable caching during development
    | for immediate reflection of changes.
    |
    */

    'cache' => [
        'enabled' => env('MTS_FEATURE_FLAGS_CACHE_ENABLED', true),
        'prefix' => env('MTS_FEATURE_FLAGS_CACHE_PREFIX', 'mts_feature_flags'),
        'ttl' => env('MTS_FEATURE_FLAGS_CACHE_TTL', 3600),
        'store' => env('MTS_FEATURE_FLAGS_CACHE_STORE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Configure the default behavior of the feature flag middleware.
    |
    */

    'middleware' => [
        'response' => env('MTS_FEATURE_FLAGS_MIDDLEWARE_RESPONSE', '404'),
        'redirect_url' => env('MTS_FEATURE_FLAGS_MIDDLEWARE_REDIRECT'),
        'json_status' => env('MTS_FEATURE_FLAGS_MIDDLEWARE_JSON_STATUS', 404),
        'json_message' => env('MTS_FEATURE_FLAGS_MIDDLEWARE_JSON_MESSAGE', 'Feature not available.'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Helper Functions
    |--------------------------------------------------------------------------
    |
    | Control whether global helper functions are registered.
    | Set to false if you experience naming conflicts.
    |
    */

    'helpers' => [
        'enabled' => env('MTS_FEATURE_FLAGS_HELPERS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Blade Directives
    |--------------------------------------------------------------------------
    |
    | Register Blade directives for feature flag checks in templates.
    |
    */

    'blade' => [
        'enabled' => env('MTS_FEATURE_FLAGS_BLADE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    |
    | Control which events are dispatched. Disable high-volume events
    | like FeatureEvaluated in production if not needed.
    |
    */

    'events' => [
        'dispatch_created' => env('MTS_FEATURE_FLAGS_EVENT_CREATED', true),
        'dispatch_updated' => env('MTS_FEATURE_FLAGS_EVENT_UPDATED', true),
        'dispatch_deleted' => env('MTS_FEATURE_FLAGS_EVENT_DELETED', true),
        'dispatch_enabled' => env('MTS_FEATURE_FLAGS_EVENT_ENABLED', true),
        'dispatch_disabled' => env('MTS_FEATURE_FLAGS_EVENT_DISABLED', true),
        'dispatch_evaluated' => env('MTS_FEATURE_FLAGS_EVENT_EVALUATED', false),
        'dispatch_override_created' => env('MTS_FEATURE_FLAGS_EVENT_OVERRIDE_CREATED', true),
        'dispatch_override_removed' => env('MTS_FEATURE_FLAGS_EVENT_OVERRIDE_REMOVED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Evaluation
    |--------------------------------------------------------------------------
    |
    | Configure feature evaluation behavior.
    |
    */

    'evaluation' => [
        'cache_evaluations' => env('MTS_FEATURE_FLAGS_CACHE_EVALUATIONS', false),
        'log_evaluations' => env('MTS_FEATURE_FLAGS_LOG_EVALUATIONS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the feature flag API endpoints.
    |
    */

    'api' => [
        'enabled' => env('MTS_FEATURE_FLAGS_API_ENABLED', false),
        'prefix' => env('MTS_FEATURE_FLAGS_API_PREFIX', 'api/feature-flags'),
        'middleware' => ['api', 'auth:sanctum'],
        'rate_limit' => [
            'enabled' => env('MTS_FEATURE_FLAGS_API_RATE_LIMIT', true),
            'max_attempts' => env('MTS_FEATURE_FLAGS_API_MAX_ATTEMPTS', 60),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    |
    | Control who can manage feature flags via API and Artisan commands.
    |
    */

    'authorization' => [
        'manage_policy' => env('MTS_FEATURE_FLAGS_MANAGE_POLICY', 'viewManageFeatureFlags'),
        'evaluate_policy' => env('MTS_FEATURE_FLAGS_EVALUATE_POLICY', 'viewFeatureFlags'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Percentage Rollout
    |--------------------------------------------------------------------------
    |
    | Configure the deterministic hashing algorithm for percentage rollouts.
    |
    */

    'rollout' => [
        'hash_algo' => env('MTS_FEATURE_FLAGS_ROLLOUT_HASH', 'crc32b'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduling
    |--------------------------------------------------------------------------
    |
    | Feature flags can be scheduled to activate/deactivate at specific times.
    | Timezone is used for evaluating schedule-based feature states.
    |
    */

    'timezone' => env('MTS_FEATURE_FLAGS_TIMEZONE'),

    /*
    |--------------------------------------------------------------------------
    | Audit Integration
    |--------------------------------------------------------------------------
    |
    | Optional integration with MTS Laravel Audit Pro.
    |
    */

    'audit' => [
        'enabled' => env('MTS_FEATURE_FLAGS_AUDIT_ENABLED', false),
    ],

];
