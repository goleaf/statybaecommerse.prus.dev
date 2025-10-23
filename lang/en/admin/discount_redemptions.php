<?php

return [
    'plural' => 'Discount Redemptions',
    'single' => 'Discount Redemption',

    'form' => [
        'sections' => [
            'basic_information' => 'Basic Information',
        ],
        'fields' => [
            'discount' => 'Discount',
            'discount_code' => 'Discount Code',
            'user' => 'User',
            'order' => 'Order',
            'discount_amount' => 'Discount Amount',
            'currency_code' => 'Currency',
            'status' => 'Status',
            'redeemed_at' => 'Redeemed At',
            'notes' => 'Notes',
            'metadata' => 'Metadata',
            'metadata_key' => 'Key',
            'metadata_value' => 'Value',
            'ip_address' => 'IP Address',
            'user_agent' => 'User Agent',
        ],
    ],

    'table' => [
        'discount_code' => 'Discount Code',
        'discount' => 'Discount',
        'user' => 'User',
        'order' => 'Order',
        'discount_amount' => 'Discount Amount',
        'status' => 'Status',
        'redeemed_at' => 'Redeemed At',
        'created_at' => 'Created At',
        'deleted' => 'Deleted',
    ],

    'filters' => [
        'discount_code' => 'Discount Code',
        'user' => 'User',
        'redeemed_at' => 'Redeemed At',
        'redeemed_from' => 'Redeemed From',
        'redeemed_until' => 'Redeemed Until',
        'recent' => 'Recent',
    ],

    'actions' => [
        'refund' => 'Refund',
        'bulk_refund' => 'Refund Selected',
    ],

    'refund_successful' => 'Successfully refunded',
    'bulk_refund_successful' => 'Selected records refunded successfully',
];
