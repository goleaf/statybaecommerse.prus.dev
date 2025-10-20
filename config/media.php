<?php

declare(strict_types=1);

return [
    'storage' => [
        'collection_name' => 'default',
        'thumbnail_collection' => 'thumbnails',
    ],

    'features' => [
        'category' => 'enabled',
        'brand' => 'enabled',
        'review' => 'enabled',
        'discount' => 'enabled',
    ],

    'max_upload_size' => env('MEDIA_MAX_UPLOAD', 10 * 1024 * 1024),

    'allowed_mime_types' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/avif',
    ],

    'variants' => [
        'thumb' => ['width' => 180, 'height' => 180],
        'medium' => ['width' => 720, 'height' => 720],
        'large' => ['width' => 1440, 'height' => 1440],
    ],
];
