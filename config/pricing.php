<?php

declare(strict_types=1);

return [
    'currency' => env('PRICING_CURRENCY', 'EUR'),
    'rounding' => [
        'precision' => (int) env('PRICING_ROUNDING_PRECISION', 2),
        'mode'      => (int) env('PRICING_ROUNDING_MODE', PHP_ROUND_HALF_UP),
    ],
    'vat' => [
        'rate'        => (float) env('PRICING_VAT_RATE', 21.0),
        'setting_key' => env('PRICING_VAT_SETTING_KEY', 'tax_rate'),
    ],
    'shipping' => [
        'flat_rate'                  => (float) env('PRICING_SHIPPING_FLAT_RATE', 5.99),
        'flat_rate_setting_key'      => env('PRICING_SHIPPING_SETTING_KEY', 'shipping_cost'),
        'free_threshold'             => (float) env('PRICING_FREE_SHIPPING_THRESHOLD', 100.0),
        'free_threshold_setting_key' => env('PRICING_FREE_SHIPPING_SETTING_KEY', 'free_shipping_threshold'),
    ],
];
