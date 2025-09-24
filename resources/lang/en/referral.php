<?php

declare(strict_types=1);

return [
    'nav' => [
        'group' => 'Referral',
    ],
    'resource' => [
        'referral_code' => [
            'section' => [
                'code_details' => 'Code Details',
            ],
        ],
    ],
    'form' => [
        'user' => 'User',
        'code' => 'Code',
        'title' => 'Title',
        'description' => 'Description',
        'is_active' => 'Active',
        'expires_at' => 'Expires At',
        'usage_limit' => 'Usage Limit',
        'usage_count' => 'Usage Count',
        'reward_amount' => 'Reward Amount',
        'reward_type' => 'Reward Type',
        'campaign_id' => 'Campaign',
        'source' => 'Source',
        'conditions' => 'Conditions (JSON)',
        'conditions_key' => 'Key',
        'conditions_value' => 'Value',
        'conditions_add' => 'Add Condition',
        'tags' => 'Tags (JSON)',
        'tags_key' => 'Tag',
        'tags_value' => 'Value',
        'tags_add' => 'Add Tag',
        'metadata' => 'Metadata (JSON)',
        'metadata_key' => 'Key',
        'metadata_value' => 'Value',
        'metadata_add' => 'Add Metadata Item',
    ],
    'columns' => [
        'is_active' => 'Active',
    ],
    'filters' => [
        'is_active' => 'Active',
        'reward_type' => 'Reward Type',
        'user' => 'User',
        'campaign_id' => 'Campaign',
    ],
    'reward_types' => [
        'discount' => 'Discount',
        'credit' => 'Credit',
        'points' => 'Points',
        'gift' => 'Gift',
    ],
];
