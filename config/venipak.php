<?php

declare(strict_types=1);

return [
    'client_id' => env('VENIPAK_CLIENT_ID', ''),
    'username'  => env('VENIPAK_USERNAME', ''),
    'password'  => env('VENIPAK_PASSWORD', ''),
    'sandbox'   => env('VENIPAK_SANDBOX', true),
    'api_url'   => env('VENIPAK_SANDBOX', true) ? 'http://venipak.uat.megodata.com/ws/' : 'https://go.venipak.lt/ws/',
];
