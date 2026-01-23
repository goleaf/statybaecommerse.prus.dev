<?php

declare(strict_types=1);

return [
    'rate_limits' => [
        'search'  => (int) env('API_RATE_LIMIT_SEARCH', 30),
        'exports' => (int) env('API_RATE_LIMIT_EXPORTS', 10),
    ],
];
