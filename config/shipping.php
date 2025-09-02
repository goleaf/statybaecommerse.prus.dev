<?php declare(strict_types=1);

return [
    // Default flat shipping (in major currency units) if no option chosen
    'default_rate' => env('SHIPPING_DEFAULT_RATE', 5.0),
    // Optional per-zone flat rates (by zone code)
    'zones' => [
        // 'EU' => 7.50,
        // 'US' => 9.90,
    ],
];
