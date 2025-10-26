<?php

declare(strict_types=1);

return [
    // Navigation
    'navigation' => [
        'partners' => 'Partners',
    ],

    // Models
    'models' => [
        'partner'  => 'Partner',
        'partners' => 'Partners',
    ],

    // Fields
    'fields' => [
        'name'            => 'Name',
        'code'            => 'Code',
        'tier'            => 'Tier',
        'is_enabled'      => 'Enabled',
        'contact_email'   => 'Contact Email',
        'contact_phone'   => 'Contact Phone',
        'discount_rate'   => 'Discount Rate',
        'commission_rate' => 'Commission Rate',
        'logo'            => 'Logo',
        'banner'          => 'Banner',
        'created_at'      => 'Created',
        'updated_at'      => 'Updated',
    ],

    // Sections
    'sections' => [
        'basic_information'   => 'Basic Information',
        'contact_information' => 'Contact Information',
        'financial_settings'  => 'Financial Settings',
        'media'               => 'Media',
    ],

    // Actions
    'actions' => [
        'create' => 'Create',
        'view'   => 'View',
        'edit'   => 'Edit',
        'delete' => 'Delete',
    ],

    // Help text
    'name_help'            => 'Partner name',
    'code_help'            => 'Unique partner code',
    'tier_help'            => 'Partner tier',
    'contact_email_help'   => 'Contact email address',
    'contact_phone_help'   => 'Contact phone number',
    'discount_rate_help'   => 'Discount rate (0-100)',
    'commission_rate_help' => 'Commission rate (0-100)',
    'logo_help'            => 'Partner logo',
    'banner_help'          => 'Partner banner',

    'dashboard' => [
        'title'        => 'Partner orders',
        'subtitle'     => 'Track partner order statuses and revenue at a glance.',
        'result_count' => ':count orders total',
        'tabs'         => [
            'open'      => 'Open',
            'shipped'   => 'Shipped',
            'cancelled' => 'Cancelled',
        ],
        'table' => [
            'order'          => 'Order',
            'status'         => 'Status',
            'payment_status' => 'Payment',
            'items'          => 'Items',
            'items_count'    => '{0}No items|{1}1 item|[2,*]:count items',
            'total'          => 'Total',
            'placed_at'      => 'Placed on',
        ],
        'empty' => [
            'title'       => 'No orders match this filter yet',
            'description' => 'Adjust the status filter or check back once new partner orders arrive.',
        ],
        'errors' => [
            'forbidden' => [
                'title'       => 'Partner access required',
                'description' => 'Your account is not linked to an active partner. Contact support to request access.',
            ],
            'unauthorized' => [
                'title'       => 'Sign in to continue',
                'description' => 'You need to sign in with a partner-enabled account to view partner orders.',
            ],
        ],
    ],
];
