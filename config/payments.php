<?php

declare(strict_types=1);

return [
    /*
     * Configure Stripe payment behaviour without assuming production secrets.
     * Each webhook entry defines the headers and tolerance used when verifying
     * provider callbacks so tests can stub the values without leaking secrets.
     */
    'stripe' => [
        'enabled' => (bool) env('PAYMENT_STRIPE_ENABLED', true),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET', 'whsec_test'),
            'signature_header' => env('STRIPE_WEBHOOK_SIGNATURE_HEADER', 'Stripe-Signature'),
            'timestamp_header' => env('STRIPE_WEBHOOK_TIMESTAMP_HEADER', 'Stripe-Timestamp'),
            'tolerance' => (int) env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    /*
     * NotchPay mirrors the Stripe defaults but keeps independent environment
     * variables so dedicated staging environments can rotate the secrets
     * without affecting other providers.
     */
    'notchpay' => [
        'enabled' => (bool) env('PAYMENT_NOTCHPAY_ENABLED', true),
        'webhook' => [
            'secret' => env('NOTCHPAY_WEBHOOK_SECRET', 'notchpay_test_secret'),
            'signature_header' => env('NOTCHPAY_WEBHOOK_SIGNATURE_HEADER', 'X-Notchpay-Signature'),
            'timestamp_header' => env('NOTCHPAY_WEBHOOK_TIMESTAMP_HEADER', 'X-Notchpay-Timestamp'),
            'tolerance' => (int) env('NOTCHPAY_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    /*
     * Cash payments do not expose webhook behaviour but the feature flag keeps
     * compatibility with the storefront payment selector.
     */
    'cash' => [
        'enabled' => (bool) env('PAYMENT_CASH_ENABLED', true),
    ],
];
