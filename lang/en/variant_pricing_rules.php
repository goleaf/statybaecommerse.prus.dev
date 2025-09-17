<?php

declare(strict_types=1);

return [
    // Navigation and Labels
    'title' => 'Variant Pricing Rules',
    'plural' => 'Variant Pricing Rules',
    'single' => 'Variant Pricing Rule',
    'navigation_label' => 'Pricing Rules',
    'navigation_group' => 'Products',

    // Tabs
    'tabs' => [
        'main' => 'Main Information',
        'basic_information' => 'Basic Information',
        'conditions' => 'Conditions',
        'pricing_modifiers' => 'Pricing Modifiers',
        'schedule' => 'Schedule',
        'all' => 'All Rules',
        'active' => 'Active Rules',
        'size_based' => 'Size Based',
        'quantity_based' => 'Quantity Based',
        'customer_group_based' => 'Customer Group Based',
        'time_based' => 'Time Based',
    ],

    // Sections
    'sections' => [
        'basic_information' => 'Basic Information',
        'conditions' => 'Rule Conditions',
        'pricing_modifiers' => 'Pricing Modifiers',
        'schedule' => 'Schedule Settings',
    ],

    // Fields
    'fields' => [
        'product' => 'Product',
        'rule_name' => 'Rule Name',
        'rule_type' => 'Rule Type',
        'priority' => 'Priority',
        'is_active' => 'Active',
        'conditions' => 'Conditions',
        'attribute' => 'Attribute',
        'operator' => 'Operator',
        'value' => 'Value',
        'pricing_modifiers' => 'Pricing Modifiers',
        'modifier_type' => 'Modifier Type',
        'modifier_value' => 'Modifier Value',
        'modifier_conditions' => 'Modifier Conditions',
        'starts_at' => 'Starts At',
        'ends_at' => 'Ends At',
        'created_at' => 'Created At',
    ],

    // Rule Types
    'rule_types' => [
        'size_based' => 'Size Based',
        'quantity_based' => 'Quantity Based',
        'customer_group_based' => 'Customer Group Based',
        'time_based' => 'Time Based',
    ],

    // Attributes
    'attributes' => [
        'size' => 'Size',
        'variant_type' => 'Variant Type',
        'price' => 'Price',
        'weight' => 'Weight',
    ],

    // Operators
    'operators' => [
        'equals' => 'Equals',
        'not_equals' => 'Not Equals',
        'greater_than' => 'Greater Than',
        'less_than' => 'Less Than',
        'contains' => 'Contains',
        'not_contains' => 'Does Not Contain',
    ],

    // Modifier Types
    'modifier_types' => [
        'percentage' => 'Percentage',
        'fixed_amount' => 'Fixed Amount',
        'multiplier' => 'Multiplier',
    ],

    // Actions
    'actions' => [
        'add_condition' => 'Add Condition',
        'add_modifier' => 'Add Modifier',
        'add_modifier_condition' => 'Add Modifier Condition',
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
    ],

    // Messages
    'messages' => [
        'created_successfully' => 'Pricing rule created successfully',
        'created_successfully_description' => 'The pricing rule has been created and is ready to use',
        'updated_successfully' => 'Pricing rule updated successfully',
        'updated_successfully_description' => 'The pricing rule has been updated with your changes',
        'bulk_activate_success' => 'Selected rules have been activated',
        'bulk_deactivate_success' => 'Selected rules have been deactivated',
    ],

    // Validation Messages
    'validation' => [
        'rule_name_required' => 'Rule name is required',
        'product_required' => 'Product is required',
        'rule_type_required' => 'Rule type is required',
        'priority_numeric' => 'Priority must be a number',
    ],

    // Help Text
    'help' => [
        'priority' => 'Higher numbers have higher priority',
        'conditions' => 'Conditions that must be met for the rule to apply',
        'pricing_modifiers' => 'How the price should be modified when conditions are met',
        'starts_at' => 'When the rule becomes active (optional)',
        'ends_at' => 'When the rule becomes inactive (optional)',
    ],
];
