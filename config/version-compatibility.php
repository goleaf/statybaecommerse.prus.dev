<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Version Compatibility Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Filament version compatibility service that handles
    | transformations between Filament v4 and v3.3 formats.
    |
    */

    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Cache Configuration
        |--------------------------------------------------------------------------
        |
        | Configure caching behavior for transformation results to improve
        | performance when processing the same content multiple times.
        |
        */
        'prefix' => env('VERSION_COMPAT_CACHE_PREFIX', 'filament_transform'),
        'ttl'    => env('VERSION_COMPAT_CACHE_TTL', 3600), // 1 hour
    ],

    'transformations' => [
        /*
        |--------------------------------------------------------------------------
        | Transformation Rules
        |--------------------------------------------------------------------------
        |
        | Configure specific transformation rules and patterns for different
        | types of Filament components.
        |
        */
        'form_schema' => [
            'enabled'     => true,
            'strict_mode' => false, // Enable for stricter validation
        ],

        'table_configuration' => [
            'enabled'           => true,
            'preserve_comments' => true,
        ],

        'heroicons' => [
            'enabled'       => true,
            'fallback_icon' => 'heroicon-o-question-mark-circle',
        ],
    ],

    'performance' => [
        /*
        |--------------------------------------------------------------------------
        | Performance Configuration
        |--------------------------------------------------------------------------
        |
        | Settings to optimize transformation performance and memory usage.
        |
        */
        'batch_size'           => env('VERSION_COMPAT_BATCH_SIZE', 50),
        'enable_gc'            => env('VERSION_COMPAT_ENABLE_GC', true),
        'memory_limit_mb'      => env('VERSION_COMPAT_MEMORY_LIMIT', 256),
        'cache_compiled_regex' => env('VERSION_COMPAT_CACHE_REGEX', true),
    ],

    'logging' => [
        /*
        |--------------------------------------------------------------------------
        | Logging Configuration
        |--------------------------------------------------------------------------
        |
        | Configure logging behavior for transformation operations and performance
        | monitoring.
        |
        */
        'slow_threshold_ms'       => env('VERSION_COMPAT_SLOW_THRESHOLD', 100),
        'log_all_transformations' => env('VERSION_COMPAT_LOG_ALL', false),
    ],

    'security' => [
        /*
        |--------------------------------------------------------------------------
        | Security Configuration
        |--------------------------------------------------------------------------
        |
        | Security settings for file processing and transformation operations.
        |
        */
        'allowed_extensions' => ['php'],
        'allowed_mime_types' => [
            'text/x-php',
            'application/x-php',
            'text/plain',
        ],
        'max_file_size'                => env('VERSION_COMPAT_MAX_FILE_SIZE', 1024 * 1024), // 1MB
        'disable_path_traversal_check' => env('VERSION_COMPAT_DISABLE_PATH_CHECK', false),
        'enable_mime_type_check'       => env('VERSION_COMPAT_ENABLE_MIME_CHECK', true),
        'enable_content_validation'    => env('VERSION_COMPAT_ENABLE_CONTENT_VALIDATION', true),

        /*
        |--------------------------------------------------------------------------
        | Rate Limiting Configuration
        |--------------------------------------------------------------------------
        |
        | Configure rate limiting to prevent abuse and DoS attacks.
        |
        */
        'rate_limiting' => [
            'enabled'              => env('VERSION_COMPAT_RATE_LIMIT_ENABLED', true),
            'max_attempts'         => env('VERSION_COMPAT_RATE_LIMIT_ATTEMPTS', 60),
            'decay_minutes'        => env('VERSION_COMPAT_RATE_LIMIT_DECAY', 60),
            'enable_ip_limiting'   => env('VERSION_COMPAT_IP_RATE_LIMIT', true),
            'enable_user_limiting' => env('VERSION_COMPAT_USER_RATE_LIMIT', true),
        ],

        /*
        |--------------------------------------------------------------------------
        | Audit Logging Configuration
        |--------------------------------------------------------------------------
        |
        | Configure security audit logging for compliance and monitoring.
        |
        */
        'audit_logging' => [
            'enabled'                   => env('VERSION_COMPAT_AUDIT_LOGGING', true),
            'log_successful_operations' => env('VERSION_COMPAT_LOG_SUCCESS', false),
            'log_failed_operations'     => env('VERSION_COMPAT_LOG_FAILURES', true),
            'log_security_events'       => env('VERSION_COMPAT_LOG_SECURITY', true),
            'include_content_hash'      => env('VERSION_COMPAT_LOG_CONTENT_HASH', true),
        ],
    ],
];
