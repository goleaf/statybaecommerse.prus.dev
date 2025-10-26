<?php

declare(strict_types=1);

return [
    'driver' => env('SCOUT_DRIVER', 'collection'),

    'prefix' => env('SCOUT_PREFIX', ''),

    'queue' => (bool) env('SCOUT_QUEUE', false),

    'after_commit' => (bool) env('SCOUT_AFTER_COMMIT', false),

    'chunk' => [
        'searchable'   => (int) env('SCOUT_CHUNK_SEARCHABLE', 500),
        'unsearchable' => (int) env('SCOUT_CHUNK_UNSEARCHABLE', 500),
    ],

    'soft_delete' => (bool) env('SCOUT_SOFT_DELETE', false),

    'identify' => (bool) env('SCOUT_IDENTIFY', false),

    'engines' => [
        'algolia' => [
            'id'     => env('ALGOLIA_APP_ID'),
            'secret' => env('ALGOLIA_SECRET'),
        ],

        'meilisearch' => [
            'host' => env('MEILISEARCH_HOST', 'http://127.0.0.1:7700'),
            'key'  => env('MEILISEARCH_KEY'),
        ],
    ],
];
