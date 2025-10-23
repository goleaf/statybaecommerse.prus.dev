<?php

declare(strict_types=1);

return [
    'driver' => env('SEARCH_DRIVER', 'database'),

    'scout' => [
        'enabled' => (bool) env('SCOUT_ENABLED', false),
        'index_prefix' => env('SCOUT_PREFIX', ''),
        'max_results' => (int) env('SEARCH_SCOUT_MAX_RESULTS', 200),
    ],
];
