<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Force Profiling
    |--------------------------------------------------------------------------
    |
    | If you want to enable profiling for non-local environments,
    | you should set this config variable to the `true` value,
    | by using the `DB_PROFILER_FORCE` in your `.env` file.
    |
    */

    'force' => env('DB_PROFILER_FORCE', false),

    /*
    |--------------------------------------------------------------------------
    | Append items name to responses data json
    |--------------------------------------------------------------------------
    */

    'append' => env('DB_PROFILER_APPEND', "queries")
];
