<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Boot Error Detection
    |--------------------------------------------------------------------------
    |
    | Configuration for detecting and handling boot-related errors.
    | These settings help identify critical application startup failures.
    |
    */

    'boot_error_detection' => [
        'enabled' => env('BOOT_ERROR_DETECTION_ENABLED', true),

        'patterns' => [
            'Interface',
            'not found',
            'undefined method',
            'Cannot declare class',
            'Fatal error',
            'Parse error',
            'Syntax error',
            'translations()',
            'TranslatableRecord',
        ],

        'paths' => [
            '/Models/',
            '/Contracts/',
            '/Providers/',
            '/bootstrap/',
        ],

        'log_channel' => env('BOOT_ERROR_LOG_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Settings for monitoring exception handling performance and metrics.
    |
    */

    'performance' => [
        'track_boot_errors'         => env('TRACK_BOOT_ERROR_METRICS', false),
        'cache_error_patterns'      => env('CACHE_ERROR_PATTERNS', true),
        'async_metrics_storage'     => env('ASYNC_METRICS_STORAGE', true),
        'metrics_queue'             => env('METRICS_QUEUE', 'metrics'),
        'cache_ttl_minutes'         => env('PERFORMANCE_CACHE_TTL', 5),
        'distributed_rate_limiting' => env('DISTRIBUTED_RATE_LIMITING', false),
        'enable_profiling'          => env('BOOT_ERROR_PROFILING_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Budgets
    |--------------------------------------------------------------------------
    |
    | Performance budgets for monitoring and alerting.
    |
    */

    'budgets' => [
        'exception_handling_max_ms'   => env('EXCEPTION_HANDLING_MAX_MS', 5),
        'boot_error_detection_max_ms' => env('BOOT_ERROR_DETECTION_MAX_MS', 2),
        'context_building_max_ms'     => env('CONTEXT_BUILDING_MAX_MS', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security settings for exception handling to prevent information
    | disclosure and injection attacks.
    |
    */

    'security' => [
        'max_message_length' => env('EXCEPTION_MAX_MESSAGE_LENGTH', 2000),
        'sanitize_paths'     => env('EXCEPTION_SANITIZE_PATHS', true),

        // Rate limiting prevents DoS attacks via boot error spam
        'rate_limit_enabled' => env('EXCEPTION_RATE_LIMIT_ENABLED', true),

        // Maximum boot errors allowed per minute before rate limiting kicks in
        // Set to 0 to block all boot error logging immediately
        'max_boot_errors_per_minute' => env('EXCEPTION_MAX_BOOT_ERRORS_PER_MINUTE', 10),

        // Memory cleanup threshold - when to clean old rate limit entries
        'rate_limit_cleanup_threshold' => env('EXCEPTION_RATE_LIMIT_CLEANUP_THRESHOLD', 60),

        'redact_sensitive_data' => env('EXCEPTION_REDACT_SENSITIVE_DATA', true),
        'log_request_id'        => env('EXCEPTION_LOG_REQUEST_ID', true),
        'prevent_log_injection' => env('EXCEPTION_PREVENT_LOG_INJECTION', true),
    ],
];
