<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Queue connection
    |--------------------------------------------------------------------------
    | Connection used for Hopper's chunked staging/commit jobs. Null uses the
    | application's default queue connection.
    */
    'queue_connection' => env('HOPPER_QUEUE_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Default chunk size
    |--------------------------------------------------------------------------
    | Rows processed per chunk when streaming a source into staging and when
    | replaying staging into the target.
    */
    'default_chunk_size' => 500,

    /*
    |--------------------------------------------------------------------------
    | Audit
    |--------------------------------------------------------------------------
    | Audit driver sink. "database" is the default; "chronicle" requires
    | laravel-chronicle/core to be installed.
    */
    'audit' => [
        'driver' => env('HOPPER_AUDIT_DRIVER', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    | Names for every hopper_* table. Override to namespace within a shared DB.
    */
    'tables' => [
        'runs' => 'hopper_runs',
        'staging' => 'hopper_staging',
        'mapping_templates' => 'hopper_mapping_templates',
        'failed_rows' => 'hopper_failed_rows',
        'audit' => 'hopper_audit',
    ],

];
