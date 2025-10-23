<?php

return [
    'title' => 'Price Lists',
    'plural' => 'Price Lists',
    'single' => 'Price List',

    // Sections
    'basic_information' => 'Basic Information',
    'availability' => 'Availability & Conditions',
    'settings' => 'Settings',

    'name' => 'Name',
    'code' => 'Code',
    'currency' => 'Currency',
    'priority' => 'Priority',
    'description' => 'Description',
    'is_enabled' => 'Enabled',
    'is_default' => 'Default',
    'auto_apply' => 'Auto-apply',
    'starts_at' => 'Starts At',
    'ends_at' => 'Ends At',
    'starts_at_from' => 'Start date from',
    'starts_at_until' => 'Start date until',
    'ends_at_from' => 'End date from',
    'ends_at_until' => 'End date until',
    'min_order_amount' => 'Minimum Order Amount',
    'max_order_amount' => 'Maximum Order Amount',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    'created_at' => 'Created at',
    'updated_at' => 'Updated at',

    'all_records' => 'All records',
    'enabled_only' => 'Enabled only',
    'disabled_only' => 'Disabled only',
    'default_only' => 'Default only',
    'non_default_only' => 'Non-default only',
    'auto_apply_only' => 'Auto-apply only',
    'manual_only' => 'Manual only',

    // Relation data
    'customer_group' => 'Customer Group',
    'discount_percentage' => 'Discount Percentage',
    'is_active' => 'Active',
    'partner' => 'Partner',
    'email' => 'Email',
    'phone' => 'Phone',
    'commission_rate' => 'Commission Rate',

    'tabs' => [
        'all' => 'All price lists',
        'active' => 'Active',
        'default' => 'Default',
        'auto_apply' => 'Auto-apply',
    ],

    // Relation managers
    'relation_managers' => [
        'customer_groups' => [
            'title' => 'Customer Groups',
        ],
        'partners' => [
            'title' => 'Partners',
        ],
        'items' => [
            'title' => 'Price List Items',
        ],
    ],

    'stats' => [
        'total_price_lists' => 'Total Price Lists',
        'total_price_lists_description' => 'All price lists in the catalogue',
        'enabled_price_lists' => 'Enabled Price Lists',
        'enabled_price_lists_description' => 'Price lists currently enabled',
        'active_price_lists' => 'Active Price Lists',
        'active_price_lists_description' => 'Price lists that are currently active',
        'default_price_lists' => 'Default Price Lists',
        'default_price_lists_description' => 'Primary price lists applied automatically',
        'auto_apply_price_lists' => 'Auto-apply Price Lists',
        'auto_apply_price_lists_description' => 'Price lists automatically applied to customers',
    ],

    'charts' => [
        'activity_over_time' => 'Price List Activity Over Time',
        'price_lists_created' => 'Price Lists Created',
    ],
];
