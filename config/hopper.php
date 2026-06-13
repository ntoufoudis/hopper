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
    | Mapping
    |--------------------------------------------------------------------------
    | Header-to-field mapping. "aliases" is a field => known-header-spellings
    | dictionary used by AliasMatch. "fuzzy_threshold" is the minimum similarity
    | (0.0–1.0) FuzzyMatch requires before it proposes a field.
    */
    'mapping' => [
        'aliases' => [
            'email' => ['e-mail', 'e-mail address', 'email address', 'mail'],
            'name' => ['full name', 'customer name'],
        ],
        'fuzzy_threshold' => 0.8,
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
