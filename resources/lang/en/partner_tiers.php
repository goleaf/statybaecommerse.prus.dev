<?php

return [
    // Navigation
    'navigation' => [
        'partner_tiers' => 'Partner Tiers',
    ],

    // Models
    'models' => [
        'partner_tier' => 'Partner Tier',
        'partner_tiers' => 'Partner Tiers',
    ],

    // Fields
    'fields' => [
        'name' => 'Name',
        'code' => 'Code',
        'is_enabled' => 'Enabled',
        'discount_rate' => 'Discount Rate',
        'commission_rate' => 'Commission Rate',
        'minimum_order_value' => 'Minimum Order Value',
        'benefits' => 'Benefits',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Basic Information',
        'financial_settings' => 'Financial Settings',
        'benefits' => 'Benefits',
    ],

    // Actions
    'actions' => [
        'create' => 'Create',
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
    ],

    // Help text
    'name_help' => 'Tier name',
    'code_help' => 'Unique tier code',
    'discount_rate_help' => 'Discount rate (0-100)',
    'commission_rate_help' => 'Commission rate (0-100)',
    'minimum_order_value_help' => 'Minimum order value (€)',
    'benefits_help' => 'Tier benefits',
];
