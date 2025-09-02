<?php declare(strict_types=1);

return [
    // Default tax rate as percentage (e.g., 21 => 21%)
    'default_rate' => env('TAX_DEFAULT_RATE', 21),
    // Optional zone-specific rates keyed by zone code or ID
    // Example: 'EU' => 21, 'US' => 0
    'zones' => [
        // 'EU' => 21,
        // 'US' => 0,
    ],
];
