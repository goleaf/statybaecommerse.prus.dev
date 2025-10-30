<?php

declare(strict_types=1);

return [
    // Base currency used when a model does not override the default reference code.
    'base_currency' => 'EUR',

    // Static fallback rates consumed by the synchroniser until a dynamic provider is introduced.
    'static_rates'  => [
        'EUR' => 1.0,
        'USD' => 1.08,
        'GBP' => 0.86,
        'SEK' => 10.45,
    ],
];
