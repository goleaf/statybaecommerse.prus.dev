<?php

declare(strict_types=1);

return [
    /*
     * |--------------------------------------------------------------------------
     * | Application Features Configuration
     * |--------------------------------------------------------------------------
     * |
     * | This file is for configuring feature toggles throughout the application.
     * | Features can be enabled/disabled to control application functionality.
     * |
     */
    'features' => [
        'attribute' => true,
        'brand' => true,
        'category' => true,
        'collection' => true,
        'discount' => true,
        'review' => true,
        'inventory' => true,
        'multi_currency' => true,
        'multi_language' => true,
        'seo' => true,
        'analytics' => true,
        'advanced_pricing' => true,
        'customer_groups' => true,
        'partner_system' => true,
        'campaigns' => true,
        'two_factor_auth' => true,
        'wishlist' => true,
        'comparison' => true,
        'recommendations' => true,
    ],

    /*
     * |--------------------------------------------------------------------------
     * | Default Currency
     * |--------------------------------------------------------------------------
     * |
     * | The default currency for the application
     * |
     */
    'currency' => env('APP_CURRENCY', 'EUR'),

    /*
     * |--------------------------------------------------------------------------
     * | Supported Locales
     * |--------------------------------------------------------------------------
     * |
     * | The locales supported by the application
     * |
     */
    'supported_locales' => ['en', 'lt'],

    /*
     * |--------------------------------------------------------------------------
     * | Default Locale
     * |--------------------------------------------------------------------------
     * |
     * | The default locale for the application
     * |
     */
    'locale' => env('APP_LOCALE', 'lt'),

    /*
     * |--------------------------------------------------------------------------
     * | Fallback Locale
     * |--------------------------------------------------------------------------
     * |
     * | The fallback locale for the application
     * |
     */
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
];
