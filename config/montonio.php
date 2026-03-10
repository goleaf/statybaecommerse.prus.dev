<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Montonio API Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Montonio credentials. You can find these
    | keys in your Montonio Partner System.
    |
    */
    'access_key' => env('MONTONIO_ACCESS_KEY', ''),
    'secret_key' => env('MONTONIO_SECRET_KEY', ''),
    'sandbox'    => env('MONTONIO_SANDBOX', true),
    'demo_mode'  => env('MONTONIO_DEMO_MODE', false),



    /*
    |--------------------------------------------------------------------------
    | Montonio API URLs
    |--------------------------------------------------------------------------
    |
    | The base URLs for Montonio API services depending on environment.
    |
    */
    'sandbox_url'    => env('MONTONIO_SANDBOX_URL', 'https://sandbox-stargate.montonio.com/api'),
    'production_url' => env('MONTONIO_PRODUCTION_URL', 'https://stargate.montonio.com/api'),
];
