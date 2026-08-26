<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Audit Driver
    |--------------------------------------------------------------------------
    |
    | The default audit driver used to store audit records. You may switch
    | drivers to store audit records in different storage backends.
    |
    | Supported: "database"
    |
    */

    'driver' => env('AUDIT_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Audit Database Connection
    |--------------------------------------------------------------------------
    |
    | The database connection to use for storing audit records. This allows
    | storing audit records in a separate database from your application.
    | Set to null to use the default application connection.
    |
    */

    'connection' => env('AUDIT_DB_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Audit Table
    |--------------------------------------------------------------------------
    |
    | The table name where audit records will be stored.
    |
    */

    'table' => 'audits',

    /*
    |--------------------------------------------------------------------------
    | Queue Audit Records
    |--------------------------------------------------------------------------
    |
    | When set to true, audit records will be queued for asynchronous
    | persistence. This improves performance for high-traffic applications
    | but requires a working queue setup.
    |
    | Note: When queue is unavailable, records will be stored synchronously
    | to prevent data loss.
    |
    */

    'queue' => env('AUDIT_QUEUE', false),

    /*
    |--------------------------------------------------------------------------
    | Queue Connection
    |--------------------------------------------------------------------------
    |
    | The queue connection to use for queued audit records.
    |
    */

    'queue_connection' => env('AUDIT_QUEUE_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Queue Name
    |--------------------------------------------------------------------------
    |
    | The queue name to use for queued audit records.
    |
    */

    'queue_name' => env('AUDIT_QUEUE_NAME', 'audit'),

    /*
    |--------------------------------------------------------------------------
    | Enabled Events
    |--------------------------------------------------------------------------
    |
    | The audit events that will be recorded. You may disable specific
    | events that are not needed for your application.
    |
    */

    'events' => [
        'created',
        'updated',
        'deleted',
        'restored',
        'retrieved',
        'login',
        'logout',
        'failed_login',
        'password_changed',
        'password_reset',
        'permission_changed',
        'role_changed',
        'exported',
        'imported',
        'approved',
        'rejected',
        'published',
        'unpublished',
        'custom',
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Excluded Fields
    |--------------------------------------------------------------------------
    |
    | Fields that should never be recorded in audit logs. These fields
    | are always excluded across all models, even if the model does not
    | define its own exclusions.
    |
    */

    'exclude' => [
        'password',
        'password_confirmation',
        'remember_token',
        'api_token',
        'secret',
        'credit_card_number',
        'ssn',
        'social_security_number',
        'cvv',
        'pin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Masking Strategies
    |--------------------------------------------------------------------------
    |
    | Define custom masking strategies for sensitive fields. Each strategy
    | is a callable that receives the field value and returns the masked
    | version.
    |
    */

    'masking' => [
        'enabled' => env('AUDIT_MASKING_ENABLED', true),

        'strategies' => [
            'email' => function ($value) {
                if (!is_string($value) || !str_contains($value, '@')) {
                    return '********';
                }
                [$name, $domain] = explode('@', $value, 2);
                $masked = substr($name, 0, 1) . str_repeat('*', max(strlen($name) - 1, 1));
                return $masked . '@' . $domain;
            },

            'phone' => function ($value) {
                if (!is_string($value)) {
                    return '********';
                }
                $digits = preg_replace('/\D/', '', $value);
                if (strlen($digits) < 4) {
                    return str_repeat('*', strlen($value));
                }
                $suffix = substr($digits, -4);
                $prefix = str_repeat('*', max(strlen($value) - 4, 0));
                return $prefix . $suffix;
            },

            'api_token' => function ($value) {
                return str_repeat('*', 8);
            },

            'token' => function ($value) {
                return str_repeat('*', 8);
            },

            'credit_card' => function ($value) {
                if (!is_string($value)) {
                    return '********';
                }
                $cleaned = preg_replace('/\D/', '', $value);
                if (strlen($cleaned) < 4) {
                    return str_repeat('*', strlen($value));
                }
                return str_repeat('*', strlen($value) - 4) . substr($cleaned, -4);
            },

            'ssn' => function ($value) {
                return '***-**-' . substr(preg_replace('/\D/', '', (string) $value), -4);
            },

            'default' => function ($value) {
                return str_repeat('*', 8);
            },
        ],

        /*
        |--------------------------------------------------------------------------
        | Masked Fields
        |--------------------------------------------------------------------------
        |
        | Fields that should be masked instead of excluded. These fields
        | will have their values replaced with masked versions.
        |
        */

        'fields' => [
            'email',
            'phone',
            'api_token',
            'token',
            'credit_card_number',
            'credit_card',
            'ssn',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Actor Resolution
    |--------------------------------------------------------------------------
    |
    | Configure how the actor (user) is resolved for audit events.
    |
    */

    'actor' => [
        'resolver' => \MageTech\Audit\Services\AuthenticatedUserResolver::class,

        /*
        | Resolve actor from API tokens.
        */
        'resolve_from_api_token' => true,

        /*
        | Default actor type when no user is authenticated.
        */
        'default_type' => 'system',
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Context
    |--------------------------------------------------------------------------
    |
    | Configure which request context information should be captured
    | with each audit record.
    |
    */

    'request' => [
        /*
        | Enable/disable request context capture entirely.
        */
        'enabled' => env('AUDIT_REQUEST_CONTEXT', true),

        /*
        | Capture the IP address.
        */
        'ip_address' => true,

        /*
        | Capture the user agent.
        */
        'user_agent' => true,

        /*
        | Capture the URL.
        */
        'url' => true,

        /*
        | Capture the HTTP method.
        */
        'method' => true,

        /*
        | Capture the route name.
        */
        'route' => true,

        /*
        | Capture the request ID (if available).
        */
        'request_id' => true,

        /*
        | Capture the session ID.
        */
        'session_id' => false,

        /*
        | Capture the authentication guard.
        */
        'auth_guard' => false,

        /*
        | Excluded URL patterns (regex).
        */
        'exclude_urls' => [
            '/telescope',
            '/horizon',
            '/sanctum/csrf-cookie',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy
    |--------------------------------------------------------------------------
    |
    | Configure tenant resolution for multi-tenant applications.
    |
    */

    'tenancy' => [
        /*
        | Enable tenant-aware auditing.
        */
        'enabled' => env('AUDIT_TENANCY_ENABLED', false),

        /*
        | The tenant resolver class.
        */
        'resolver' => \MageTech\Audit\Services\DefaultTenantResolver::class,

        /*
        | The column name for tenant_id in the audits table.
        */
        'column' => 'tenant_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Integrity Verification
    |--------------------------------------------------------------------------
    |
    | Configure hash chaining for tamper detection. This provides tamper
    | evidence, not absolute tamper prevention.
    |
    */

    'integrity' => [
        /*
        | Enable hash chaining.
        */
        'enabled' => env('AUDIT_INTEGRITY_ENABLED', false),

        /*
        | The hashing algorithm to use.
        */
        'algorithm' => 'sha256',

        /*
        | Whether to verify integrity on read.
        */
        'verify_on_read' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention Policy
    |--------------------------------------------------------------------------
    |
    | Configure automatic cleanup of old audit records.
    |
    */

    'retention' => [
        /*
        | Enable automatic retention cleanup.
        */
        'enabled' => env('AUDIT_RETENTION_ENABLED', false),

        /*
        | Number of days to keep audit records.
        */
        'days' => 365,

        /*
        | Run cleanup automatically via scheduled task.
        */
        'auto_cleanup' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Serialization
    |--------------------------------------------------------------------------
    |
    | Configure how values are serialized for storage.
    |
    */

    'serialization' => [
        /*
        | The maximum depth for serialization to prevent circular references.
        */
        'max_depth' => 10,

        /*
        | Maximum string length for serialized values.
        */
        'max_string_length' => 65535,

        /*
        | Whether to cast objects to arrays for storage.
        */
        'cast_objects_to_array' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorized Permissions
    |--------------------------------------------------------------------------
    |
    | The permissions required for various audit operations.
    |
    */

    'permissions' => [
        'view' => 'audit.view',
        'view_details' => 'audit.view_details',
        'export' => 'audit.export',
        'delete' => 'audit.delete',
        'manage' => 'audit.manage',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for audit API endpoints.
    |
    */

    'api' => [
        'enabled' => env('AUDIT_API_ENABLED', true),
        'rate_limit' => 60,
        'rate_limit_per_minute' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dispatched Events
    |--------------------------------------------------------------------------
    |
    | Configure which events are dispatched when audit records are created.
    |
    */

    'dispatch_events' => [
        'created' => \MageTech\Audit\Events\AuditCreated::class,
        'queued' => \MageTech\Audit\Events\AuditQueued::class,
        'stored' => \MageTech\Audit\Events\AuditStored::class,
        'failed' => \MageTech\Audit\Events\AuditFailed::class,
    ],

];
