<?php

declare(strict_types=1);

return [
    'rate_limits' => [
        'default'       => (int) env('API_RATE_LIMIT_DEFAULT', 60),
        'notifications' => (int) env('API_RATE_LIMIT_NOTIFICATIONS', 60),
        'autocomplete'  => (int) env('API_RATE_LIMIT_AUTOCOMPLETE', 30),
        'search'        => (int) env('API_RATE_LIMIT_SEARCH', 30),
        'exports'       => (int) env('API_RATE_LIMIT_EXPORTS', 10),
    ],
];
