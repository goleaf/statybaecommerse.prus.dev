<?php

declare(strict_types=1);

return [
    'consent' => [
        'cookie_name'          => env('COOKIE_CONSENT_NAME', 'statybae_cookie_consent'),
        'cookie_lifetime_days' => (int) env('COOKIE_CONSENT_LIFETIME_DAYS', 365),
    ],
    'retention' => [
        'logs'  => (int) env('LOG_RETENTION_DAYS', 30),
        'audit' => (int) env('AUDIT_LOG_RETENTION_DAYS', 365),
    ],
];
