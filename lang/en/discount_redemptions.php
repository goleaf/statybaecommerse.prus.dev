<?php

declare(strict_types=1);

return [
    'title'  => 'Discount Redemptions',
    'plural' => 'Discount Redemptions',
    'single' => 'Discount Redemption',

    'sections' => [
        'associations'           => 'Associations',
        'redemption_details'     => 'Redemption Details',
        'additional_information' => 'Additional Information',
    ],

    'fields' => [
        'discount'       => 'Discount',
        'code'           => 'Discount Code',
        'user'           => 'Customer',
        'order'          => 'Order',
        'amount_saved'   => 'Amount Saved',
        'currency_code'  => 'Currency',
        'status'         => 'Status',
        'redeemed_at'    => 'Redeemed At',
        'ip_address'     => 'IP Address',
        'user_agent'     => 'User Agent',
        'notes'          => 'Notes',
        'metadata'       => 'Metadata',
        'metadata_key'   => 'Key',
        'metadata_value' => 'Value',
    ],

    'statuses' => [
        'pending'   => 'Pending',
        'redeemed'  => 'Redeemed',
        'expired'   => 'Expired',
        'cancelled' => 'Cancelled',
    ],

    'filters' => [
        'redeemed_from'  => 'Redeemed From',
        'redeemed_until' => 'Redeemed Until',
        'has_order'      => 'Has Order',
    ],
];
