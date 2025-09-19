<?php

return [
    'title' => 'Collection Rules',
    'plural' => 'Collection Rules',
    'single' => 'Collection Rule',
    'form' => [
        'tabs' => [
            'basic_information' => 'Basic Information',
            'rule_details' => 'Rule Details',
        ],
        'sections' => [
            'basic_information' => 'Basic Information',
            'rule_details' => 'Rule Details',
        ],
        'fields' => [
            'collection' => 'Collection',
            'field' => 'Field',
            'operator' => 'Operator',
            'value' => 'Value',
            'position' => 'Position',
            'collection_name' => 'Collection Name',
            'rule_description' => 'Rule Description',
            'start_position' => 'Start Position',
            'created_at' => 'Created At',
        ],
    ],
    'operators' => [
        'equals' => 'Equals',
        'not_equals' => 'Not Equals',
        'contains' => 'Contains',
        'not_contains' => 'Not Contains',
        'starts_with' => 'Starts With',
        'ends_with' => 'Ends With',
        'greater_than' => 'Greater Than',
        'less_than' => 'Less Than',
        'greater_than_or_equal' => 'Greater Than or Equal',
        'less_than_or_equal' => 'Less Than or Equal',
    ],
    'filters' => [
        'collection' => 'Collection',
        'operator' => 'Operator',
        'created_at' => 'Created At',
        'recent' => 'Recent (30 days)',
    ],
    'actions' => [
        'reorder' => 'Reorder',
        'reorder_bulk' => 'Bulk Reorder',
    ],
    'notifications' => [
        'reordered_successfully' => 'Reordered successfully',
        'bulk_reordered_successfully' => 'Bulk reordered successfully',
    ],
];

