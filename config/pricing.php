<?php

declare(strict_types=1);

return [
    'currency' => env('PRICING_DEFAULT_CURRENCY', 'EUR'),
    'rounding_precision' => (int) env('PRICING_ROUNDING_PRECISION', 2),
    'shipping' => [
        'flat_rate' => (float) env('PRICING_SHIPPING_FLAT_RATE', 5.99),
        'free_threshold' => (float) env('PRICING_FREE_SHIPPING_THRESHOLD', 50.0),
    ],
];
