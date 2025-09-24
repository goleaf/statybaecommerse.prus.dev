<?php

return [
    /*
    |--------------------------------------------------------------------------
    | European Date Time Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for European date and time formatting
    | used throughout the application.
    |
    */

    'european_locales' => [
        'lt', 'lv', 'et', 'pl', 'sk', 'cs', 'hu', 'ro', 'bg', 'hr', 'sl', 'de',
    ],

    'formats' => [
        'date' => 'Y-m-d',
        'datetime' => 'Y-m-d H:i',
        'datetime_full' => 'Y-m-d H:i:s',
        'time' => 'H:i',
        'date_short' => 'y-m-d',
        'month_year' => 'Y-m',
        'year_month' => 'Y-m',
    ],

    'filament_formats' => [
        'date' => 'Y-m-d',
        'datetime' => 'Y-m-d H:i',
        'datetime_full' => 'Y-m-d H:i:s',
    ],

    'fallback_formats' => [
        'date' => 'Y-m-d',
        'datetime' => 'Y-m-d H:i',
        'datetime_full' => 'Y-m-d H:i:s',
    ],

    'timezone' => 'Europe/Vilnius',
];
