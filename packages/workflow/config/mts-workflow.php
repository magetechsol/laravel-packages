<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Workflow Definitions
    |--------------------------------------------------------------------------
    |
    | Configure how workflow definitions are stored and resolved.
    |
    */

    'definitions' => [
        'register' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Retry Settings
    |--------------------------------------------------------------------------
    |
    | Default retry behavior for workflow steps that can fail.
    |
    */

    'retry' => [
        'max_attempts' => env('MTS_WORKFLOW_MAX_ATTEMPTS', 3),
        'backoff' => env('MTS_WORKFLOW_BACKOFF', 'exponential'),
        'base_delay' => env('MTS_WORKFLOW_BASE_DELAY', 60),
        'max_delay' => env('MTS_WORKFLOW_MAX_DELAY', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Timeout
    |--------------------------------------------------------------------------
    |
    | Default timeout in seconds for workflow steps.
    |
    */

    'timeout' => env('MTS_WORKFLOW_TIMEOUT', 300),

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the queue connection and queue name for async workflow steps.
    |
    */

    'queue' => [
        'connection' => env('MTS_WORKFLOW_QUEUE_CONNECTION'),
        'queue' => env('MTS_WORKFLOW_QUEUE', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Concurrency
    |--------------------------------------------------------------------------
    |
    | Prevent duplicate execution of the same workflow step.
    |
    */

    'concurrency' => [
        'enabled' => env('MTS_WORKFLOW_CONCURRENCY_ENABLED', true),
        'lock_timeout' => env('MTS_WORKFLOW_LOCK_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Approval Defaults
    |--------------------------------------------------------------------------
    |
    | Default settings for approval steps.
    |
    */

    'approvals' => [
        'default_timeout' => env('MTS_WORKFLOW_APPROVAL_TIMEOUT', 86400),
        'allow_self_approval' => env('MTS_WORKFLOW_ALLOW_SELF_APPROVAL', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | Configure audit trail behavior.
    |
    */

    'audit' => [
        'enabled' => env('MTS_WORKFLOW_AUDIT_ENABLED', true),
        'log_request_id' => env('MTS_WORKFLOW_LOG_REQUEST_ID', true),
        'log_ip_address' => env('MTS_WORKFLOW_LOG_IP', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pruning
    |--------------------------------------------------------------------------
    |
    | Configure automatic pruning of old workflow records.
    |
    */

    'pruning' => [
        'enabled' => env('MTS_WORKFLOW_PRUNING_ENABLED', false),
        'retain_days' => env('MTS_WORKFLOW_RETAIN_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    |
    | Control who can start, approve, or cancel workflows.
    |
    */

    'authorization' => [
        'start_policy' => env('MTS_WORKFLOW_START_POLICY', 'any'),
        'cancel_policy' => env('MTS_WORKFLOW_CANCEL_POLICY', 'creator'),
    ],

];
