<?php

declare(strict_types=1);

return [
    'headers' => [
        'enabled' => (bool) env('SECURITY_HEADERS_ENABLED', true),
        'values'  => [
            'X-Frame-Options'              => 'DENY',
            'X-Content-Type-Options'       => 'nosniff',
            'Referrer-Policy'              => 'strict-origin-when-cross-origin',
            'Cross-Origin-Opener-Policy'   => 'same-origin',
            'Cross-Origin-Resource-Policy' => 'same-site',
        ],
        'hsts' => [
            'enabled'            => (bool) env('SECURITY_HEADERS_HSTS_ENABLED', true),
            'max_age'            => (int) env('SECURITY_HEADERS_HSTS_MAX_AGE', 31536000),
            'include_subdomains' => (bool) env('SECURITY_HEADERS_HSTS_INCLUDE_SUBDOMAINS', true),
            'preload'            => (bool) env('SECURITY_HEADERS_HSTS_PRELOAD', false),
        ],
        'permissions_policy' => [
            'accelerometer'   => [],
            'camera'          => [],
            'geolocation'     => [],
            'gyroscope'       => [],
            'magnetometer'    => [],
            'microphone'      => [],
            'payment'         => [],
            'usb'             => [],
            'fullscreen'      => ['self'],
            'display-capture' => [],
        ],
        'content_security_policy' => [
            'use_nonce'  => (bool) env('SECURITY_HEADERS_CSP_USE_NONCE', true),
            'directives' => [
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
                'script-src-attr' => ["'none'"],
                'style-src'       => [
                    "'self'",
                    '@nonce',
                    'https://fonts.bunny.net',
                    'https://unpkg.com',
                ],
                'style-src-attr' => ["'self'"],
                'font-src'       => [
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
                'frame-src' => [
                    "'self'",
                ],
                'manifest-src' => [
                    "'self'",
                ],
                'media-src' => [
                    "'self'",
                ],
                'worker-src' => [
                    "'self'",
                    'blob:',
                ],
                'upgrade-insecure-requests' => [],
            ],
        ],
    ],
    'rate_limiting' => [
        'defaults' => [
            'minute' => (int) env('API_RATE_LIMIT_DEFAULT_FALLBACK', 60),
        ],
        'api' => [
            'default' => [
                'minute' => (int) env('API_RATE_LIMIT_DEFAULT', 60),
                'hour'   => (int) env('API_RATE_LIMIT_DEFAULT_HOURLY', 600),
            ],
            'notifications' => [
                'minute' => (int) env('API_RATE_LIMIT_NOTIFICATIONS', 60),
                'hour'   => (int) env('API_RATE_LIMIT_NOTIFICATIONS_HOURLY', 240),
            ],
            'autocomplete' => [
                'minute' => (int) env('API_RATE_LIMIT_AUTOCOMPLETE', 30),
                'hour'   => (int) env('API_RATE_LIMIT_AUTOCOMPLETE_HOURLY', 120),
            ],
            'profile' => [
                'minute' => (int) env('API_RATE_LIMIT_PROFILE', 60),
            ],
        ],
        'frontend' => [
            'checkout' => [
                'minute' => (int) env('FRONTEND_RATE_LIMIT_CHECKOUT', 10),
            ],
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
