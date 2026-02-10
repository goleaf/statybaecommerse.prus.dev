<?php

declare(strict_types=1);

return [
    'industry' => [
        'construction'  => 'Construction',
        'education'     => 'Education',
        'finance'       => 'Finance',
        'healthcare'    => 'Healthcare',
        'manufacturing' => 'Manufacturing',
        'other'         => 'Other',
        'retail'        => 'Retail',
        'technology'    => 'Technology',
    ],
    'organization_type' => [
        'company'    => 'Company',
        'department' => 'Department',
        'team'       => 'Team',
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
