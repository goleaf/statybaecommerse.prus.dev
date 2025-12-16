<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Search Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default search driver that will be used for
    | search operations. You may set this to any of the drivers defined
    | in the "drivers" configuration array below.
    |
    | Supported: "scout", "database"
    |
    */
    'driver' => env('SEARCH_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Search Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the search drivers for your application. Each
    | driver has its own configuration options that you may customize.
    |
    */
    'drivers' => [
        'scout' => [
            'enabled'      => (bool) env('SCOUT_ENABLED', false),
            'index_prefix' => env('SCOUT_PREFIX', ''),
            'max_results'  => (int) env('SEARCH_SCOUT_MAX_RESULTS', 200),
            'fallback'     => env('SCOUT_FALLBACK_TO_DATABASE', true),
        ],

        'database' => [
            'optimize_for_production' => (bool) env('DB_SEARCH_OPTIMIZE_PRODUCTION', true),
            'enable_full_text'        => (bool) env('DB_SEARCH_ENABLE_FULLTEXT', false),
            'cache_ttl'               => (int) env('DB_SEARCH_CACHE_TTL', 300), // 5 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Result Caching
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for search results. Cache TTL values are
    | in seconds. Tags are used for targeted cache invalidation when
    | catalog data changes.
    |
    */
    'cache' => [
        'enabled'     => (bool) env('SEARCH_CACHE_ENABLED', true),
        'default_ttl' => (int) env('SEARCH_CACHE_DEFAULT_TTL', 3600), // 1 hour
        'popular_ttl' => (int) env('SEARCH_CACHE_POPULAR_TTL', 7200), // 2 hours
        'recent_ttl'  => (int) env('SEARCH_CACHE_RECENT_TTL', 1800),  // 30 minutes
        'tags'        => [
            'enabled'         => (bool) env('SEARCH_CACHE_TAGS_ENABLED', true),
            'include_locale'  => true,
            'include_catalog' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy Scout Configuration (Deprecated)
    |--------------------------------------------------------------------------
    |
    | This configuration is maintained for backward compatibility.
    | Use the 'drivers.scout' configuration above for new implementations.
    |
    */
    'scout' => [
        'enabled'      => (bool) env('SCOUT_ENABLED', false),
        'index_prefix' => env('SCOUT_PREFIX', ''),
        'max_results'  => (int) env('SEARCH_SCOUT_MAX_RESULTS', 200),
    ],
];
