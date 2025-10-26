<?php

declare(strict_types=1);

return [
    /*
     * ISO 3166-1 alpha-2 country codes that are permitted when storing or updating
     * customer addresses. Adjust the value in the environment variable to extend
     * the allow-list without modifying source control.
     */
    'allowed_countries' => array_values(array_filter(array_map(
        static fn (string $code): string => strtoupper(trim($code)),
        explode(',', env('ADDRESS_ALLOWED_COUNTRIES', 'LT'))
    ))),

    /*
     * Regions/provinces that we explicitly accept per country. The keys must use
     * ISO 3166-1 alpha-2 country codes. The values should be arrays of the human
     * readable region names that can be selected through the UI.
     */
    'allowed_regions' => [
        'LT' => [
            'Alytus County',
            'Kaunas County',
            'Klaipėda County',
            'Marijampolė County',
            'Panevėžys County',
            'Šiauliai County',
            'Tauragė County',
            'Telšiai County',
            'Utena County',
            'Vilnius County',
        ],
    ],

    /*
     * Regular expressions keyed by country code that describe acceptable postal
     * code formats for the allow-listed countries.
     */
    'postal_code_patterns' => [
        'LT' => '/^(LT-)?\d{5}$/',
    ],
];
