<?php

declare(strict_types=1);

return [
    'dead_letter' => [
        'enabled'    => env('QUEUE_DEAD_LETTER_ENABLED', true),
        'connection' => env('QUEUE_DEAD_LETTER_CONNECTION', env('QUEUE_CONNECTION', 'database')),
        'queue'      => env('QUEUE_DEAD_LETTER_QUEUE', 'dead-letter'),
    ],

    'alerts' => [
        'enabled'           => env('QUEUE_FAILURE_ALERTS', true),
        'failure_threshold' => (int) env('QUEUE_FAILURE_SPIKE_THRESHOLD', 5),
        'window_seconds'    => (int) env('QUEUE_FAILURE_SPIKE_WINDOW', 300),
    ],
];
