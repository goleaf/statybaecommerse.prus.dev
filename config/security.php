<?php

declare(strict_types=1);

return [
    'headers' => [
        'x_frame_options' => 'DENY',
        'x_content_type_options' => 'nosniff',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'geolocation=(), microphone=(), camera=(), payment=(), usb=()',
        'csp_report_only' => "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self';",
    ],
    'rate_limiting' => [
        'login' => [
            'max_attempts' => 5,
            'decay_seconds' => 60,
        ],
        'password_reset' => [
            'max_attempts' => 3,
            'decay_seconds' => 600,
        ],
        'api' => [
            'max_attempts' => 60,
            'decay_seconds' => 60,
        ],
    ],
];
