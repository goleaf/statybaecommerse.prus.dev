<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Deterministic Totals Service Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the deterministic totals quoting service. We
    | expose a short allow-list of delivery services together with sensible
    | defaults for caching durations so downstream consumers receive stable
    | totals. Service identifiers act as public API values and must therefore
    | remain backward compatible once introduced.
    |
    */

    'cache_ttl' => env('DETERMINISTIC_TOTALS_CACHE_TTL', 3600),
    'cache_store' => env('DETERMINISTIC_TOTALS_CACHE_STORE', 'array'),

    'services' => [
        'standard' => [
            'label' => 'Standard Delivery',
            'amount' => 6.95,
        ],
        'express' => [
            'label' => 'Express Delivery',
            'amount' => 12.50,
        ],
        'pickup' => [
            'label' => 'Click & Collect',
            'amount' => 0.0,
        ],
    ],
];
