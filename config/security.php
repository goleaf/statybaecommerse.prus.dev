<?php

declare(strict_types=1);

return [
    'headers' => [
        'enabled' => (bool) env('SECURITY_HEADERS_ENABLED', true),
        'values'  => [
            'X-Frame-Options'        => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            'Permissions-Policy'     => implode(', ', [
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
            'default-src'     => ["'self'"],
            'base-uri'        => ["'self'"],
            'form-action'     => ["'self'"],
            'frame-ancestors' => ["'none'"],
            'object-src'      => ["'none'"],
            'script-src'      => [
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
        'defaults' => [
            'minute' => (int) env('API_RATE_LIMIT_DEFAULT_FALLBACK', 60),
        ],
        'api' => [
            'default'       => (int) env('API_RATE_LIMIT_DEFAULT', 60),
            'notifications' => (int) env('API_RATE_LIMIT_NOTIFICATIONS', 60),
            'autocomplete'  => (int) env('API_RATE_LIMIT_AUTOCOMPLETE', 30),
            'exports'       => (int) env('API_RATE_LIMIT_EXPORTS', 30),
        ],
        'auth' => [
            'login' => [
                'max_attempts'  => (int) env('AUTH_RATE_LIMIT_LOGIN_ATTEMPTS', 5),
                'decay_seconds' => (int) env('AUTH_RATE_LIMIT_LOGIN_DECAY', 60),
            ],
            'password_reset' => [
                'max_attempts'  => (int) env('AUTH_RATE_LIMIT_PASSWORD_RESET_ATTEMPTS', 5),
                'decay_seconds' => (int) env('AUTH_RATE_LIMIT_PASSWORD_RESET_DECAY', 300),
            ],
        ],
    ],
    'captcha' => [
        'auth' => [
            'login' => [
                'enabled'     => (bool) env('AUTH_CAPTCHA_LOGIN_ENABLED', true),
                'threshold'   => (int) env('AUTH_CAPTCHA_LOGIN_THRESHOLD', 3),
                'ttl_seconds' => (int) env('AUTH_CAPTCHA_LOGIN_TTL', 600),
            ],
            'password_reset' => [
                'enabled'     => (bool) env('AUTH_CAPTCHA_PASSWORD_RESET_ENABLED', true),
                'threshold'   => (int) env('AUTH_CAPTCHA_PASSWORD_RESET_THRESHOLD', 3),
                'ttl_seconds' => (int) env('AUTH_CAPTCHA_PASSWORD_RESET_TTL', 600),
            ],
        ],
    ],
    'monitoring' => [
        'suspicious_ip' => [
            'enabled'       => (bool) env('SECURITY_MONITORING_SUSPICIOUS_IP_ENABLED', true),
            'threshold'     => (int) env('SECURITY_MONITORING_SUSPICIOUS_IP_THRESHOLD', 10),
            'decay_seconds' => (int) env('SECURITY_MONITORING_SUSPICIOUS_IP_DECAY', 900),
        ],
    ],
];
