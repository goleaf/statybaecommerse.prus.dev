<?php

return [
    'sections' => [
        'basic_info' => 'Basic Information',
        'translations' => 'Translations',
        'advanced' => 'Advanced Settings',
    ],

    'fields' => [
        'discount' => 'Discount',
        'type' => 'Type',
        'operator' => 'Operator',
        'value' => 'Value',
        'position' => 'Position',
        'priority' => 'Priority',
        'is_active' => 'Active',
        'name' => 'Name',
        'description' => 'Description',
        'metadata' => 'Metadata',
        'metadata_key' => 'Key',
        'metadata_value' => 'Value',
        'locale' => 'Locale',
        'translations' => 'Translations',
        'condition' => 'Condition',
        'test_value' => 'Test Value',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    'types' => [
        'product' => 'Product',
        'category' => 'Category',
        'brand' => 'Brand',
        'collection' => 'Collection',
        'attribute_value' => 'Attribute Value',
        'cart_total' => 'Cart Total',
        'item_qty' => 'Item Quantity',
        'zone' => 'Zone',
        'channel' => 'Channel',
        'currency' => 'Currency',
        'customer_group' => 'Customer Group',
        'user' => 'User',
        'partner_tier' => 'Partner Tier',
        'first_order' => 'First Order',
        'day_time' => 'Day Time',
        'custom_script' => 'Custom Script',
    ],

    'operators' => [
        'equals_to' => 'Equals To',
        'not_equals_to' => 'Not Equals To',
        'less_than' => 'Less Than',
        'greater_than' => 'Greater Than',
        'less_than_or_equal' => 'Less Than or Equal',
        'greater_than_or_equal' => 'Greater Than or Equal',
        'starts_with' => 'Starts With',
        'ends_with' => 'Ends With',
        'contains' => 'Contains',
        'not_contains' => 'Not Contains',
        'in_array' => 'In Array',
        'not_in_array' => 'Not In Array',
        'regex' => 'Regular Expression',
        'not_regex' => 'Not Regular Expression',
    ],

    'helpers' => [
        'position' => 'Order of condition execution',
        'priority' => 'Condition importance (higher number = higher priority)',
        'metadata' => 'Additional data in JSON format',
        'numeric_value' => 'Enter a numeric value',
        'string_value' => 'Enter a string value',
        'array_value' => 'Enter values separated by commas',
        'regex_value' => 'Enter a regular expression',
        'general_value' => 'Enter a value according to condition type',
        'test_value' => 'Enter a value to test against the condition',
    ],

    'actions' => [
        'test_condition' => 'Test Condition',
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
        'set_priority' => 'Set Priority',
        'view' => 'View',
    ],

    'filters' => [
        'high_priority' => 'High Priority',
        'low_priority' => 'Low Priority',
        'numeric_conditions' => 'Numeric Conditions',
        'string_conditions' => 'String Conditions',
    ],

    'tabs' => [
        'all' => 'All',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'high_priority' => 'High Priority',
        'numeric' => 'Numeric',
        'string' => 'String',
    ],

    'stats' => [
        'total_conditions' => 'Total Conditions',
        'total_conditions_description' => 'Total number of conditions',
        'active_conditions' => 'Active Conditions',
        'active_conditions_description' => 'Currently active',
        'inactive_conditions' => 'Inactive Conditions',
        'inactive_conditions_description' => 'Currently inactive',
        'high_priority_conditions' => 'High Priority',
        'high_priority_conditions_description' => 'Priority > 5',
    ],

    'charts' => [
        'conditions_by_type' => 'Conditions by Type',
    ],

    'messages' => [
        'condition_matches' => 'Condition matches the specified value',
        'condition_does_not_match' => 'Condition does not match the specified value',
    ],

    'notifications' => [
        'created' => 'Condition created successfully',
        'updated' => 'Condition updated successfully',
        'deleted' => 'Condition deleted successfully',
    ],
];
