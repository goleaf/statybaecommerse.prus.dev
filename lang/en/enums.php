<?php

declare(strict_types=1);

return [
    'industry'       => [
        'construction'  => 'Construction',
        'technology'    => 'Technology',
        'manufacturing' => 'Manufacturing',
        'retail'        => 'Retail',
        'finance'       => 'Finance',
        'education'     => 'Education',
        'healthcare'    => 'Healthcare',
        'other'         => 'Other',
    ],
    'organization_type' => [
        'company'    => 'Company',
        'team'       => 'Team',
        'department' => 'Department',
    ],
    'payment_method' => [
        'apple_pay'        => 'Apple Pay',
        'bank_transfer'    => 'Bank transfer',
        'cash_on_delivery' => 'Cash on delivery',
        'credit_card'      => 'Credit card',
        'google_pay'       => 'Google Pay',
        'paypal'           => 'PayPal',
        'stripe'           => 'Stripe',
    ],
    'payment_status' => [
        'authorized'         => 'Authorized',
        'captured'           => 'Captured',
        'failed'             => 'Failed',
        'paid'               => 'Paid',
        'partially_refunded' => 'Partially refunded',
        'pending'            => 'Pending',
        'refunded'           => 'Refunded',
        'settled'            => 'Settled',
    ],
    'priority' => 'enums.priority',
    'status'   => 'enums.status',
];
