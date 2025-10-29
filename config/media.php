<?php

declare(strict_types=1);

return [
    'storage' => [
        'collection_name'      => 'default',
        'thumbnail_collection' => 'thumbnails',
    ],

    'urls' => [
        'use_temporary_urls'     => env('MEDIA_USE_TEMPORARY_URLS', false),
        'temporary_url_ttl'      => (int) env('MEDIA_TEMPORARY_URL_TTL', 60),
        'response_cache_control' => env('MEDIA_RESPONSE_CACHE_CONTROL', 'public, max-age=604800'),
    ],

    'features' => [
        'category' => 'enabled',
        'brand'    => 'enabled',
        'review'   => 'enabled',
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
        'thumb'  => ['width' => 180, 'height' => 180],
        'medium' => ['width' => 720, 'height' => 720],
        'large'  => ['width' => 1440, 'height' => 1440],
    ],

    'placeholders' => [
        'app' => [
            'fallback' => 'images/placeholder.jpg',
        ],
        'product' => [
            'uuid'     => env('MEDIA_PLACEHOLDER_PRODUCT_UUID'),
            'fallback' => 'images/placeholder-product.jpg',
            'variants' => [
                'thumb'   => 'thumb',
                'medium'  => 'medium',
                'large'   => 'large',
                'default' => null,
            ],
        ],
        'product_png' => [
            'uuid'     => env('MEDIA_PLACEHOLDER_PRODUCT_PNG_UUID'),
            'fallback' => 'images/placeholder-product.png',
            'variants' => [
                'thumb'   => 'thumb',
                'medium'  => 'medium',
                'large'   => 'large',
                'default' => null,
            ],
        ],
        'og' => [
            'uuid'     => env('MEDIA_PLACEHOLDER_OG_UUID'),
            'fallback' => 'images/og-default.jpg',
        ],
    ],
];
