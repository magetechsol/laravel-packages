<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | The disk used for storing uploaded and exported files.
    |
    */

    'disk' => env('MTS_IMPORT_EXPORT_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configure file upload constraints and allowed types.
    |
    */

    'upload' => [
        'max_file_size' => env('MTS_IMPORT_MAX_FILE_SIZE', 10240), // KB
        'allowed_extensions' => ['csv', 'xlsx', 'json', 'xml'],
        'allowed_mime_types' => [
            'text/csv',
            'text/plain',
            'application/csv',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/json',
            'application/xml',
            'text/xml',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Import Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how imports are processed.
    |
    */

    'import' => [
        'chunk_size' => env('MTS_IMPORT_CHUNK_SIZE', 1000),
        'batch_size' => env('MTS_IMPORT_BATCH_SIZE', 500),
        'queue_connection' => env('MTS_IMPORT_QUEUE_CONNECTION', 'redis'),
        'queue_name' => env('MTS_IMPORT_QUEUE_NAME', 'imports'),
        'timeout' => env('MTS_IMPORT_TIMEOUT', 600),
        'tries' => env('MTS_IMPORT_TRIES', 3),
        'max_exceptions' => env('MTS_IMPORT_MAX_EXCEPTIONS', 10),
        'retry_after' => env('MTS_IMPORT_RETRY_AFTER', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how exports are processed.
    |
    */

    'export' => [
        'chunk_size' => env('MTS_EXPORT_CHUNK_SIZE', 1000),
        'queue_connection' => env('MTS_EXPORT_QUEUE_CONNECTION', 'redis'),
        'queue_name' => env('MTS_EXPORT_QUEUE_NAME', 'exports'),
        'timeout' => env('MTS_EXPORT_TIMEOUT', 600),
        'tries' => env('MTS_EXPORT_TRIES', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Configuration
    |--------------------------------------------------------------------------
    |
    | Configure row-level and file-level validation behavior.
    |
    */

    'validation' => [
        'require_header' => true,
        'max_rows' => env('MTS_IMPORT_MAX_ROWS', null),
        'stop_on_failure' => false,
        'duplicate_detection' => 'ignore', // ignore | reject | upsert
        'unique_keys' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security measures for file handling.
    |
    */

    'security' => [
        'sanitize_filenames' => true,
        'formula_injection_protection' => true,
        'max_file_size' => env('MTS_IMPORT_MAX_FILE_SIZE', 10240), // KB
        'validate_mime_real' => true,
        'prevent_path_traversal' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Handling Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how errors are tracked and reported.
    |
    */

    'error_handling' => [
        'log_errors' => true,
        'max_errors_per_import' => env('MTS_IMPORT_MAX_ERRORS', 1000),
        'generate_error_report' => true,
        'error_report_format' => 'csv', // csv | xlsx
    ],

    /*
    |--------------------------------------------------------------------------
    | Progress Tracking Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how progress updates are dispatched.
    |
    */

    'progress' => [
        'update_interval' => env('MTS_IMPORT_PROGRESS_INTERVAL', 100), // rows between events
        'dispatch_events' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Configuration
    |--------------------------------------------------------------------------
    |
    | Set to false if you want to handle persistence yourself.
    |
    */

    'tables' => [
        'enabled' => true,
        'imports' => 'imports',
        'import_rows' => 'import_rows',
        'import_errors' => 'import_errors',
        'exports' => 'exports',
    ],

    /*
    |--------------------------------------------------------------------------
    | CSV Configuration
    |--------------------------------------------------------------------------
    |
    | Default CSV parsing options.
    |
    */

    'csv' => [
        'delimiter' => ',',
        'enclosure' => '"',
        'escape' => '\\',
        'encoding' => 'UTF-8',
        'has_header' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | XML Configuration
    |--------------------------------------------------------------------------
    |
    | Configure XML parsing behavior.
    |
    */

    'xml' => [
        'root_element' => 'records',
        'row_element' => 'record',
        'encoding' => 'UTF-8',
    ],

];
