<?php

return [
    'enabled' => env('SECURITY_HEADERS_ENABLED', true),

    'headers' => [
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
];
