<?php

declare(strict_types=1);

return [
    'metrics' => [
        // Store observability metrics in Redis by default to avoid exhausting database-backed caches.
        'cache_store' => env('OBSERVABILITY_METRICS_CACHE_STORE', env('CACHE_STORE', 'redis')),
        'cache_key'   => env('OBSERVABILITY_METRICS_CACHE_KEY', 'monitoring:cache_metrics'),
        'queue_key'   => env('OBSERVABILITY_QUEUE_METRICS_CACHE_KEY', 'monitoring:queue_metrics'),
    ],
];
