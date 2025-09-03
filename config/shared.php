<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shared Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains shared configuration values used across the application
    | for UI components, services, and common functionality.
    |
    */

    'ui' => [
        'default_button_variant' => 'primary',
        'default_button_size' => 'md',
        'default_card_padding' => 'p-6',
        'default_card_shadow' => 'shadow-md',
        'default_input_size' => 'md',
        'default_pagination_per_page' => 12,
        'max_products_per_page' => 48,
        'max_compare_products' => 4,
    ],

    'cache' => [
        'default_ttl' => 3600, // 1 hour
        'short_ttl' => 900,     // 15 minutes
        'long_ttl' => 86400,    // 24 hours
        'home_cache_ttl' => 1800, // 30 minutes
        'product_cache_ttl' => 3600, // 1 hour
        'category_cache_ttl' => 7200, // 2 hours
    ],

    'localization' => [
        'default_locale' => 'lt',
        'supported_locales' => ['lt', 'en', 'de'],
        'default_currency' => 'EUR',
        'locale_currency_mapping' => [
            'lt' => 'EUR',
            'en' => 'EUR',
            'de' => 'EUR',
        ],
        'locale_timezone_mapping' => [
            'lt' => 'Europe/Vilnius',
            'en' => 'UTC',
            'de' => 'Europe/Berlin',
        ],
    ],

    'seo' => [
        'default_title_suffix' => config('app.name'),
        'default_description_length' => 160,
        'default_og_image' => '/images/og-default.jpg',
        'structured_data_enabled' => true,
    ],

    'media' => [
        'default_collection' => 'gallery',
        'thumbnail_sizes' => [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600],
        ],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'svg'],
        'max_file_size' => 5120, // 5MB in KB
    ],

    'features' => [
        'wishlist_enabled' => true,
        'compare_enabled' => true,
        'reviews_enabled' => true,
        'ratings_enabled' => true,
        'newsletter_enabled' => true,
        'search_suggestions' => true,
        'recently_viewed' => true,
    ],

    'validation' => [
        'max_search_length' => 255,
        'min_password_length' => 8,
        'max_review_length' => 2000,
        'max_comment_length' => 500,
        'phone_pattern' => '/^(\+370|370|8)[0-9]{8}$/',
    ],

    'performance' => [
        'lazy_loading_enabled' => true,
        'image_optimization' => true,
        'css_minification' => env('APP_ENV') === 'production',
        'js_minification' => env('APP_ENV') === 'production',
        'gzip_compression' => true,
    ],
];