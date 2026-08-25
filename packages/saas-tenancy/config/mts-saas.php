<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Tenancy Strategy
    |--------------------------------------------------------------------------
    |
    | Choose how tenants are isolated in the database.
    |
    |   'shared'  - Single database, all tenants share tables with tenant_id column
    |   'database' - Each tenant gets its own database
    |
    | Switching strategies requires running the appropriate migration command.
    |
    */

    'strategy' => env('MTS_SAAS_STRATEGY', 'shared'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Model
    |--------------------------------------------------------------------------
    |
    | The Eloquent model used to represent tenants. Override this if you need
    | a custom tenant model with additional relationships or logic.
    |
    */

    'model' => env('MTS_SAAS_TENANT_MODEL', \MageTech\SaaS\Models\Tenant::class),

    /*
    |--------------------------------------------------------------------------
    | Tenant Resolver
    |--------------------------------------------------------------------------
    |
    | How the current tenant is identified from an incoming request.
    | Multiple resolvers can be active — the first match wins.
    |
    | Available: 'subdomain', 'domain', 'path', 'header', 'session', 'cookie'
    |
    */

    'resolvers' => [

        'default' => env('MTS_SAAS_RESOLVER', 'subdomain'),

        'subdomain' => [
            'enabled' => true,
            'root_domain' => env('MTS_SAAS_ROOT_DOMAIN', 'example.com'),
        ],

        'domain' => [
            'enabled' => false,
            'mapping' => [],
        ],

        'path' => [
            'enabled' => false,
            'prefix' => 'tenant',
        ],

        'header' => [
            'enabled' => false,
            'header_name' => 'X-Tenant-ID',
        ],

        'session' => [
            'enabled' => false,
            'key' => 'tenant_id',
        ],

        'cookie' => [
            'enabled' => false,
            'name' => 'tenant_id',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Central Domain
    |--------------------------------------------------------------------------
    |
    | The domain(s) that are NOT tenant-specific (e.g., your main app domain).
    | Routes on these domains won't require tenant identification.
    |
    */

    'central_domains' => [
        env('APP_DOMAIN', 'localhost'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Key Column
    |--------------------------------------------------------------------------
    |
    | The column used to identify tenants in shared database strategy.
    | This column must exist on all tenant-scoped tables.
    |
    */

    'key_column' => env('MTS_SAAS_KEY_COLUMN', 'tenant_id'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Key Type
    |--------------------------------------------------------------------------
    |
    | The type of tenant identifier used throughout the system.
    |
    |   'uuid' - UUID v4 (recommended)
    |   'ulid' - ULID (sortable)
    |   'int'  - Auto-incrementing integer
    |
    */

    'key_type' => env('MTS_SAAS_KEY_TYPE', 'uuid'),

    /*
    |--------------------------------------------------------------------------
    | Database Prefix (Database-Per-Tenant Strategy)
    |--------------------------------------------------------------------------
    |
    | The prefix used for tenant database names when using the 'database' strategy.
    | Each tenant database will be named: {prefix}_{tenant_id}
    |
    */

    'database' => [

        'prefix' => env('MTS_SAAS_DATABASE_PREFIX', 'tenant'),

    ],

    /*
    |--------------------------------------------------------------------------
    | User Tenancy
    |--------------------------------------------------------------------------
    |
    | Configure how users relate to tenants.
    |
    |   table: The pivot table for user-tenant relationships
    |   column: The tenant key column on the pivot table
    |   user_column: The user key column on the pivot table
    |   role_column: Column storing the user's role within the tenant
    |
    */

    'users' => [

        'table' => 'mts_tenant_users',

        'column' => 'tenant_id',

        'user_column' => 'user_id',

        'role_column' => 'role',

        'default_role' => 'member',

    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant-Scoped Tables
    |--------------------------------------------------------------------------
    |
    | List of tables that should be scoped to tenants (shared strategy only).
    | These tables will have the tenant_id column added automatically.
    |
    */

    'scoped_tables' => [
        'orders',
        'products',
        'invoices',
        'contacts',
        'activities',
        'notes',
        'documents',
        'tasks',
        'comments',
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Tables
    |--------------------------------------------------------------------------
    |
    | Tables that should NEVER be tenant-scoped (always global).
    |
    */

    'excluded_tables' => [
        'mts_tenants',
        'mts_tenant_users',
        'mts_tenant_roles',
        'users',
        'password_reset_tokens',
        'sessions',
        'cache',
        'jobs',
        'failed_jobs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Isolation
    |--------------------------------------------------------------------------
    |
    | Prefix cache keys per tenant to prevent data leakage.
    |
    */

    'cache' => [

        'enabled' => env('MTS_SAAS_CACHE_ENABLED', true),

        'prefix' => 'tenant',

    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Isolation
    |--------------------------------------------------------------------------
    |
    | Prefix queue names per tenant for job isolation.
    |
    */

    'queue' => [

        'enabled' => env('MTS_SAAS_QUEUE_ENABLED', true),

        'prefix' => 'tenant',

        'default_connection' => env('MTS_SAAS_QUEUE_CONNECTION'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Isolation
    |--------------------------------------------------------------------------
    |
    | Isolate file storage per tenant using separate disks or path prefixes.
    |
    |   strategy: 'prefix' (shared disk with tenant prefix) or 'disk' (separate disk per tenant)
    |   disk: Base disk name for 'prefix' strategy
    |   prefix: Path prefix for 'prefix' strategy
    |
    */

    'storage' => [

        'enabled' => env('MTS_SAAS_STORAGE_ENABLED', true),

        'strategy' => 'prefix',

        'disk' => env('MTS_SAAS_STORAGE_DISK', 'local'),

        'prefix' => 'tenants',

    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Isolation
    |--------------------------------------------------------------------------
    |
    | Route notifications through tenant-specific channels or config.
    |
    */

    'notifications' => [

        'enabled' => env('MTS_SAAS_NOTIFICATIONS_ENABLED', true),

    ],

    /*
    |--------------------------------------------------------------------------
    | Migrations
    |--------------------------------------------------------------------------
    |
    | Configure how tenant migrations are handled.
    |
    |   path: Path to tenant-specific migration files
    |   connection: Database connection to use for migrations
    |
    */

    'migrations' => [

        'path' => database_path('migrations/tenants'),

        'connection' => env('MTS_SAAS_MIGRATION_CONNECTION'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Suspension
    |--------------------------------------------------------------------------
    |
    | Configure tenant suspension behaviour.
    |
    |   redirect_to: URL to redirect suspended tenants to
    |   message: Flash message for suspended tenants
    |
    */

    'suspension' => [

        'redirect_to' => '/tenant-suspended',

        'message' => 'Your account has been suspended. Please contact support.',

    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    |
    | Log tenant lifecycle events (created, activated, suspended, deleted).
    |
    */

    'activity_log' => [

        'enabled' => env('MTS_SAAS_ACTIVITY_LOG_ENABLED', true),

        'table' => 'mts_tenant_activity',

    ],

];
