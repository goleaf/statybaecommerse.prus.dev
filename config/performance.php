<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for performance optimization features including caching,
    | serialization, and production optimizations.
    |
    */

    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Cache Serialization
        |--------------------------------------------------------------------------
        |
        | Enable optimized cache serialization to reduce memory usage and
        | improve Livewire hydration performance.
        |
        */
        'optimize_serialization' => env('CACHE_OPTIMIZE_SERIALIZATION', true),

        /*
        |--------------------------------------------------------------------------
        | Cache Warming
        |--------------------------------------------------------------------------
        |
        | Enable automatic cache warming for critical storefront data.
        |
        */
        'enable_warming' => env('CACHE_ENABLE_WARMING', true),

        /*
        |--------------------------------------------------------------------------
        | Cache Hit Optimization
        |--------------------------------------------------------------------------
        |
        | Prevent redundant cache writes when content hasn't changed.
        |
        */
        'prevent_redundant_writes' => env('CACHE_PREVENT_REDUNDANT_WRITES', true),
    ],

    'framework' => [
        /*
        |--------------------------------------------------------------------------
        | Framework Optimizations
        |--------------------------------------------------------------------------
        |
        | Enable Laravel framework optimizations for production deployment.
        |
        */
        'enable_optimizations' => env('FRAMEWORK_ENABLE_OPTIMIZATIONS', true),

        /*
        |--------------------------------------------------------------------------
        | Optimization Commands
        |--------------------------------------------------------------------------
        |
        | Commands to run during deployment for optimal performance.
        |
        */
        'optimization_commands' => [
            'config:cache',
            'route:cache',
            'view:cache',
            'event:cache',
        ],
    ],

    'monitoring' => [
        /*
        |--------------------------------------------------------------------------
        | Performance Monitoring
        |--------------------------------------------------------------------------
        |
        | Enable performance monitoring and metrics collection.
        |
        */
        'enabled' => env('PERFORMANCE_MONITORING_ENABLED', true),

        /*
        |--------------------------------------------------------------------------
        | Query Budget Enforcement
        |--------------------------------------------------------------------------
        |
        | Maximum number of queries allowed per request type.
        |
        */
        'query_budgets' => [
            'home' => 15,
            'category' => 20,
            'product' => 25,
            'search' => 10,
            'api' => 5,
        ],

        /*
        |--------------------------------------------------------------------------
        | Memory Budget Enforcement
        |--------------------------------------------------------------------------
        |
        | Maximum memory usage allowed per request type (in MB).
        |
        */
        'memory_budgets' => [
            'home' => 64,
            'category' => 96,
            'product' => 128,
            'search' => 48,
            'api' => 32,
        ],

        /*
        |--------------------------------------------------------------------------
        | TTFB Budget Enforcement
        |--------------------------------------------------------------------------
        |
        | Maximum time to first byte allowed per request type (in ms).
        |
        */
        'ttfb_budgets' => [
            'home' => 500,
            'category' => 750,
            'product' => 600,
            'search' => 400,
            'api' => 200,
        ],
    ],

    'redis' => [
        /*
        |--------------------------------------------------------------------------
        | Redis Configuration
        |--------------------------------------------------------------------------
        |
        | Redis settings for cache and session storage.
        |
        */
        'enabled' => env('REDIS_ENABLED', false),
        'cache_prefix' => env('REDIS_CACHE_PREFIX', 'egistatyba_cache'),
        'session_prefix' => env('REDIS_SESSION_PREFIX', 'egistatyba_session'),
    ],
];