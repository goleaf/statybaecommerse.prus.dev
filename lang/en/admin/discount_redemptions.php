<?php

return [
    'plural' => 'Discount Redemptions',
    'single' => 'Discount Redemption',

    'form' => [
        'sections' => [
            'basic_information' => 'Basic Information',
        ],
        'fields' => [
            'discount_code' => 'Discount Code',
            'user' => 'User',
            'order' => 'Order',
            'discount_amount' => 'Discount Amount',
            'redeemed_at' => 'Redeemed At',
        ],
    ],

    'table' => [
        'discount_code' => 'Discount Code',
        'user' => 'User',
        'order' => 'Order',
        'discount_amount' => 'Discount Amount',
        'redeemed_at' => 'Redeemed At',
        'created_at' => 'Created At',
    ],

    'filters' => [
        'discount_code' => 'Discount Code',
        'user' => 'User',
        'redeemed_at' => 'Redeemed At',
        'recent' => 'Recent',
    ],

    'actions' => [
        'refund' => 'Refund',
        'bulk_refund' => 'Refund Selected',
    ],

    'refund_successful' => 'Successfully refunded',
    'bulk_refund_successful' => 'Selected records refunded successfully',
];
