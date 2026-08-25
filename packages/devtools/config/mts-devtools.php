<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Master switch for the DevTools package. When false, all routes,
    | commands, and the dashboard are completely disabled.
    | Defaults to false in production for safety.
    |
    */

    'enabled' => env('MTS_DEVTOOLS_ENABLED', env('APP_ENV') !== 'production'),

    /*
    |--------------------------------------------------------------------------
    | Dashboard Prefix
    |--------------------------------------------------------------------------
    |
    | The URL prefix for the web dashboard route.
    |
    */

    'prefix' => env('MTS_DEVTOOLS_PREFIX', 'devtools'),

    /*
    |--------------------------------------------------------------------------
    | Allowed IPs
    |--------------------------------------------------------------------------
    |
    | IP addresses allowed to access the dashboard and use commands.
    | Defaults to localhost only. Set to ['*'] to allow all IPs
    | (not recommended in production).
    |
    */

    'allowed_ips' => array_filter(
        explode(',', env('MTS_DEVTOOLS_ALLOWED_IPS', '127.0.0.1,::1'))
    ),

    /*
    |--------------------------------------------------------------------------
    | Password
    |--------------------------------------------------------------------------
    |
    | Optional password to protect the dashboard. Leave null to disable
    | password protection (IP restriction still applies).
    |
    */

    'password' => env('MTS_DEVTOOLS_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    |
    | Enable or disable the web dashboard. Commands may still be available
    | even when the dashboard is disabled.
    |
    */

    'dashboard' => env('MTS_DEVTOOLS_DASHBOARD', true),

    /*
    |--------------------------------------------------------------------------
    | Commands
    |--------------------------------------------------------------------------
    |
    | Enable or disable the artisan commands. Useful if you only want
    | the dashboard and not the CLI tools.
    |
    */

    'commands' => env('MTS_DEVTOOLS_COMMANDS', true),

    /*
    |--------------------------------------------------------------------------
    | Collectors
    |--------------------------------------------------------------------------
    |
    | Enable or disable individual data collectors.
    | Disabling a collector hides its section from the dashboard
    | and skips it in artisan commands.
    |
    */

    'collectors' => [

        'application' => env('MTS_DEVTOOLS_COLLECT_APPLICATION', true),

        'performance' => env('MTS_DEVTOOLS_COLLECT_PERFORMANCE', true),

        'security' => env('MTS_DEVTOOLS_COLLECT_SECURITY', true),

        'packages' => env('MTS_DEVTOOLS_COLLECT_PACKAGES', true),

    ],

    /*
    |--------------------------------------------------------------------------
    | Production Restrictions
    |--------------------------------------------------------------------------
    |
    | Configure how the package behaves in production environments.
    |
    |   auto_disable: Automatically disable when APP_ENV is production
    |   require_password: Force password protection in production
    |   log_access: Log all dashboard access attempts
    |
    */

    'production' => [

        'auto_disable' => env('MTS_DEVTOOLS_PROD_AUTO_DISABLE', true),

        'require_password' => env('MTS_DEVTOOLS_PROD_REQUIRE_PASSWORD', true),

        'log_access' => env('MTS_DEVTOOLS_PROD_LOG_ACCESS', true),

    ],

    /*
    |--------------------------------------------------------------------------
    | Slow Query Threshold
    |--------------------------------------------------------------------------
    |
    | Queries exceeding this threshold (in milliseconds) are considered
    | slow when parsing log files.
    |
    */

    'slow_query_threshold' => (int) env('MTS_DEVTOOLS_SLOW_QUERY_THRESHOLD', 1000),

    /*
    |--------------------------------------------------------------------------
    | Log Path
    |--------------------------------------------------------------------------
    |
    | Path to the Laravel log file for parsing slow queries and errors.
    | Uses the configured logging channel's path by default.
    |
    */

    'log_path' => env('MTS_DEVTOOLS_LOG_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Refresh Interval
    |--------------------------------------------------------------------------
    |
    | Auto-refresh interval for the dashboard in seconds.
    | Set to 0 to disable auto-refresh.
    |
    */

    'refresh_interval' => (int) env('MTS_DEVTOOLS_REFRESH_INTERVAL', 30),

];
