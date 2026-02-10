<?php

declare(strict_types=1);

return [
    'industry' => [
        'construction'  => 'Bau',
        'education'     => 'Bildung',
        'finance'       => 'Finanzen',
        'healthcare'    => 'Gesundheitswesen',
        'manufacturing' => 'Fertigung',
        'other'         => 'Sonstiges',
        'retail'        => 'Einzelhandel',
        'technology'    => 'Technologie',
    ],
    'organization_type' => [
        'company'    => 'Unternehmen',
        'department' => 'Abteilung',
        'team'       => 'Team',
    ],
    'payment_method' => [
        'apple_pay'        => 'Apple Pay',
        'bank_transfer'    => 'Banküberweisung',
        'cash_on_delivery' => 'Nachnahme',
        'credit_card'      => 'Kreditkarte',
        'google_pay'       => 'Google Pay',
        'paypal'           => 'PayPal',
        'stripe'           => 'Stripe',
    ],
    'payment_status' => [
        'authorized'         => 'Autorisiert',
        'captured'           => 'Eingezogen',
        'failed'             => 'Fehlgeschlagen',
        'paid'               => 'Bezahlt',
        'partially_refunded' => 'Teilweise erstattet',
        'pending'            => 'Ausstehend',
        'refunded'           => 'Erstattet',
        'settled'            => 'Abgerechnet',
    ],
    'priority' => 'enums.priority',
    'status'   => 'enums.status',
];
