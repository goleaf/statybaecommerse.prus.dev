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

    'access_key' => env('MONTONIO_ACCESS_KEY', '137fc106-2631-4bc6-96cf-595d699c8a28'),
    'secret_key' => env('MONTONIO_SECRET_KEY', 'ZGcQhGeoL2bmyluOezhXI8EtUEADHy/CPUfOIFOXYOag'),
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
    'sandbox_url'    => 'https://sandbox-stargate.montonio.com/api',
    'production_url' => 'https://stargate.montonio.com/api',
];
