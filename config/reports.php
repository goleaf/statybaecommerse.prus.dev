<?php

return [
    /*
     * |--------------------------------------------------------------------------
     * | Reports Configuration
     * |--------------------------------------------------------------------------
     * |
     * | Configuration settings for the Reports system
     * |
     */
    'cache' => [
        'enabled' => env('REPORTS_CACHE_ENABLED', true),
        'ttl' => env('REPORTS_CACHE_TTL', 3600),  // 1 hour
        'prefix' => 'reports',
    ],
    'performance' => [
        'max_records_per_query' => env('REPORTS_MAX_RECORDS', 10000),
        'query_timeout' => env('REPORTS_QUERY_TIMEOUT', 30),
        'memory_limit' => env('REPORTS_MEMORY_LIMIT', '512M'),
    ],
    'export' => [
        'formats' => ['pdf', 'csv', 'xlsx'],
        'max_export_records' => env('REPORTS_MAX_EXPORT', 50000),
        'temp_directory' => storage_path('app/reports/temp'),
    ],
    'date_ranges' => [
        'default' => 'last_30_days',
        'available' => [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last_7_days' => 'Last 7 Days',
            'last_30_days' => 'Last 30 Days',
            'last_90_days' => 'Last 90 Days',
            'this_year' => 'This Year',
            'custom' => 'Custom Range',
        ],
    ],
    'widgets' => [
        'refresh_interval' => env('REPORTS_WIDGET_REFRESH', 30),  // seconds
        'lazy_loading' => env('REPORTS_LAZY_LOADING', true),
        'polling_enabled' => env('REPORTS_POLLING_ENABLED', true),
    ],
    'security' => [
        'rate_limiting' => [
            'enabled' => env('REPORTS_RATE_LIMIT_ENABLED', true),
            'max_requests' => env('REPORTS_MAX_REQUESTS', 60),
            'per_minutes' => env('REPORTS_RATE_LIMIT_WINDOW', 1),
        ],
        'ip_whitelist' => env('REPORTS_IP_WHITELIST', ''),
    ],
    'notifications' => [
        'slow_query_threshold' => env('REPORTS_SLOW_QUERY_THRESHOLD', 5),  // seconds
        'error_notification_email' => env('REPORTS_ERROR_EMAIL', ''),
        'daily_summary_enabled' => env('REPORTS_DAILY_SUMMARY', false),
    ],
    'database' => [
        'connection' => env('REPORTS_DB_CONNECTION', 'default'),
        'chunk_size' => env('REPORTS_CHUNK_SIZE', 1000),
        'use_read_replica' => env('REPORTS_USE_READ_REPLICA', false),
    ],
    'localization' => [
        'default_locale' => env('REPORTS_DEFAULT_LOCALE', 'lt'),
        'supported_locales' => ['en', 'lt'],
        'currency' => env('REPORTS_CURRENCY', 'EUR'),
        'timezone' => env('REPORTS_TIMEZONE', 'Europe/Vilnius'),
    ],
];
