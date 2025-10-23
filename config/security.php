<?php

declare(strict_types=1);

return [
    'headers' => [
        'enabled' => (bool) env('SECURITY_HEADERS_ENABLED', true),
        'values' => [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => implode(', ', [
                'accelerometer=()',
                'camera=()',
                'fullscreen=(self)',
                'geolocation=()',
                'gyroscope=()',
                'magnetometer=()',
                'microphone=()',
                'payment=()',
                'usb=()',
                'display-capture=()',
            ]),
        ],
        'hsts' => [
            'enabled' => (bool) env('SECURITY_HSTS_ENABLED', true),
            'max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 63072000),
            'include_subdomains' => (bool) env('SECURITY_HSTS_INCLUDE_SUBDOMAINS', true),
            'preload' => (bool) env('SECURITY_HSTS_PRELOAD', false),
            'enforce_on_http' => (bool) env('SECURITY_HSTS_ENFORCE_ON_HTTP', false),
        ],
        'content_security_policy' => [
            'default-src' => ["'self'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'none'"],
            'object-src' => ["'none'"],
            'script-src' => [
                "'self'",
                '@nonce',
                'https://unpkg.com',
            ],
            'script-src-attr' => [
                "'unsafe-inline'",
            ],
            'style-src' => [
                "'self'",
                '@nonce',
                'https://fonts.bunny.net',
                'https://unpkg.com',
            ],
            'style-src-attr' => [
                "'unsafe-inline'",
            ],
            'font-src' => [
                "'self'",
                'https://fonts.bunny.net',
                'data:',
            ],
            'img-src' => [
                "'self'",
                'data:',
                'blob:',
            ],
            'connect-src' => [
                "'self'",
            ],
        ],
    ],
    'rate_limiting' => [
        'api' => [
            'default' => [
                'user' => [
                    'max_attempts' => (int) env('API_RATE_LIMIT_DEFAULT_USER_MAX_ATTEMPTS', 120),
                    'decay_seconds' => (int) env('API_RATE_LIMIT_DEFAULT_USER_DECAY', 60),
                ],
                'ip' => [
                    'max_attempts' => (int) env('API_RATE_LIMIT_DEFAULT_IP_MAX_ATTEMPTS', 240),
                    'decay_seconds' => (int) env('API_RATE_LIMIT_DEFAULT_IP_DECAY', 120),
                ],
                'global' => [
                    'max_attempts' => (int) env('API_RATE_LIMIT_DEFAULT_GLOBAL_MAX_ATTEMPTS', 1200),
                    'decay_seconds' => (int) env('API_RATE_LIMIT_DEFAULT_GLOBAL_DECAY', 60),
                ],
            ],
            'notifications' => [
                'user' => [
                    'max_attempts' => (int) env('API_RATE_LIMIT_NOTIFICATIONS_USER_MAX_ATTEMPTS', 90),
                    'decay_seconds' => (int) env('API_RATE_LIMIT_NOTIFICATIONS_USER_DECAY', 60),
                ],
                'ip' => [
                    'max_attempts' => (int) env('API_RATE_LIMIT_NOTIFICATIONS_IP_MAX_ATTEMPTS', 180),
                    'decay_seconds' => (int) env('API_RATE_LIMIT_NOTIFICATIONS_IP_DECAY', 120),
                ],
                'global' => [
                    'max_attempts' => (int) env('API_RATE_LIMIT_NOTIFICATIONS_GLOBAL_MAX_ATTEMPTS', 600),
                    'decay_seconds' => (int) env('API_RATE_LIMIT_NOTIFICATIONS_GLOBAL_DECAY', 300),
                ],
            ],
            'autocomplete' => [
                'user' => [
                    'max_attempts' => (int) env('API_RATE_LIMIT_AUTOCOMPLETE_USER_MAX_ATTEMPTS', 45),
                    'decay_seconds' => (int) env('API_RATE_LIMIT_AUTOCOMPLETE_USER_DECAY', 60),
                ],
                'ip' => [
                    'max_attempts' => (int) env('API_RATE_LIMIT_AUTOCOMPLETE_IP_MAX_ATTEMPTS', 90),
                    'decay_seconds' => (int) env('API_RATE_LIMIT_AUTOCOMPLETE_IP_DECAY', 120),
                ],
                'global' => [
                    'max_attempts' => (int) env('API_RATE_LIMIT_AUTOCOMPLETE_GLOBAL_MAX_ATTEMPTS', 300),
                    'decay_seconds' => (int) env('API_RATE_LIMIT_AUTOCOMPLETE_GLOBAL_DECAY', 300),
                ],
            ],
        ],
        'auth' => [
            'login' => [
                'max_attempts' => (int) env('AUTH_RATE_LIMIT_LOGIN_ATTEMPTS', 5),
                'decay_seconds' => (int) env('AUTH_RATE_LIMIT_LOGIN_DECAY', 60),
            ],
            'password_reset' => [
                'max_attempts' => (int) env('AUTH_RATE_LIMIT_PASSWORD_RESET_ATTEMPTS', 5),
                'decay_seconds' => (int) env('AUTH_RATE_LIMIT_PASSWORD_RESET_DECAY', 300),
            ],
        ],
    ],
];
