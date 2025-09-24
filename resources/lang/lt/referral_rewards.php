<?php

return [
    'title' => 'Referral Rewards',
    'plural' => 'Referral Rewards',
    'single' => 'Referral Reward',

    'sections' => [
        'basic_info' => 'Basic Information',
        'basic_info_description' => 'Essential details about the referral reward',
        'status_info' => 'Status Information',
        'status_info_description' => 'Current status and timing details',
        'content' => 'Content',
        'content_description' => 'Title, description and priority settings',
        'advanced' => 'Advanced Settings',
        'advanced_description' => 'Conditions, reward data and metadata',
        'timestamps' => 'Timestamps',
    ],

    'fields' => [
        'referral_id' => 'Referral Code',
        'referral_code' => 'Referral Code',
        'user_id' => 'User',
        'user_name' => 'User Name',
        'order_id' => 'Order',
        'type' => 'Type',
        'amount' => 'Amount',
        'currency_code' => 'Currency',
        'status' => 'Status',
        'applied_at' => 'Applied At',
        'expires_at' => 'Expires At',
        'is_active' => 'Active',
        'title' => 'Title',
        'description' => 'Description',
        'priority' => 'Priority',
        'conditions' => 'Conditions',
        'condition_key' => 'Condition Key',
        'condition_value' => 'Condition Value',
        'reward_data' => 'Reward Data',
        'reward_key' => 'Reward Key',
        'reward_value' => 'Reward Value',
        'metadata' => 'Metadata',
        'metadata_key' => 'Metadata Key',
        'metadata_value' => 'Metadata Value',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    'types' => [
        'discount' => 'Discount',
        'credit' => 'Credit',
        'points' => 'Points',
        'gift' => 'Gift',
    ],

    'status' => [
        'pending' => 'Pending',
        'active' => 'Active',
        'applied' => 'Applied',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
    ],

    'filters' => [
        'is_active' => 'Active',
        'type' => 'Type',
        'status' => 'Status',
        'referral' => 'Referral',
        'user' => 'User',
        'expired' => 'Expired',
        'expiring_soon' => 'Expiring Soon',
    ],

    'actions' => [
        'apply' => 'Apply',
        'expire' => 'Expire',
        'apply_selected' => 'Apply Selected',
        'expire_selected' => 'Expire Selected',
        'add_condition' => 'Add Condition',
        'add_reward_data' => 'Add Reward Data',
        'add_metadata' => 'Add Metadata',
    ],

    'notifications' => [
        'applied_successfully' => 'Reward applied successfully',
        'expired_successfully' => 'Reward expired successfully',
        'bulk_applied_successfully' => 'Selected rewards applied successfully',
        'bulk_expired_successfully' => 'Selected rewards expired successfully',
    ],

    'placeholders' => [
        'not_applied' => 'Not applied',
        'no_expiry' => 'No expiry date',
        'no_description' => 'No description',
        'no_conditions' => 'No conditions',
        'no_reward_data' => 'No reward data',
        'no_metadata' => 'No metadata',
    ],
];
