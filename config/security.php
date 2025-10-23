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
                'geolocation=()',
                'gyroscope=()',
                'magnetometer=()',
                'microphone=()',
                'payment=()',
                'usb=()',
            ]),
        ],
        'content_security_policy' => [
            'default-src' => ["'self'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'none'"],
            'object-src' => ["'none'"],
            'script-src' => [
                "'self'",
                "'unsafe-inline'",
                "'unsafe-eval'",
                'https://unpkg.com',
            ],
            'style-src' => [
                "'self'",
                "'unsafe-inline'",
                'https://fonts.bunny.net',
                'https://unpkg.com',
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
            'default' => (int) env('API_RATE_LIMIT_DEFAULT', 60),
            'notifications' => (int) env('API_RATE_LIMIT_NOTIFICATIONS', 60),
            'autocomplete' => (int) env('API_RATE_LIMIT_AUTOCOMPLETE', 30),
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
