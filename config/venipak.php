<?php

declare(strict_types=1);

return [
    'client_id' => env('VENIPAK_CLIENT_ID', '35971'),
    'username'  => env('VENIPAK_USERNAME', 'egisstatyba'),
    'password'  => env('VENIPAK_PASSWORD', '9m5kgue1a'),
    'sandbox'   => env('VENIPAK_SANDBOX', true),
    'api_url'   => env('VENIPAK_SANDBOX', true) ? 'http://venipak.uat.megodata.com/ws/' : 'https://go.venipak.lt/ws/',
];
