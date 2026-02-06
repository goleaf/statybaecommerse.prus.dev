<?php

declare(strict_types=1);

return [
    'industry' => [
        'construction' => 'Statyba',
        'education' => 'Švietimas',
        'finance' => 'Finansai',
        'healthcare' => 'Sveikatos priežiūra',
        'manufacturing' => 'Gamyba',
        'other' => 'Kita',
        'retail' => 'Mažmeninė prekyba',
        'technology' => 'Technologijos',
    ],
    'organization_type' => [
        'company' => 'Įmonė',
        'department' => 'Skyrius',
        'team' => 'Komanda',
    ],
    'payment_method' => [
        'apple_pay' => 'Apple Pay',
        'bank_transfer' => 'Banko pavedimas',
        'cash_on_delivery' => 'Apmokėjimas pristatymo metu',
        'credit_card' => 'Kreditinė kortelė',
        'google_pay' => 'Google Pay',
        'paypal' => 'PayPal',
        'stripe' => 'Stripe',
    ],
    'payment_status' => [
        'authorized' => 'Autorizuota',
        'captured' => 'Nuskaityta',
        'failed' => 'Nepavyko',
        'paid' => 'Apmokėta',
        'partially_refunded' => 'Iš dalies grąžinta',
        'pending' => 'Laukiama',
        'refunded' => 'Grąžinta',
        'settled' => 'Atsiskaityta',
    ],
    'priority' => 'enums.priority',
    'status' => 'enums.status',
];
