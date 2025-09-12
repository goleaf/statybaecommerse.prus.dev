<?php

return [
    'navigation_label' => 'Referral Codes',
    'model_label' => 'Referral Code',
    'plural_model_label' => 'Referral Codes',

    'sections' => [
        'code_information' => 'Code Information',
        'translatable_content' => 'Translatable Content',
        'usage_settings' => 'Usage Settings',
        'reward_settings' => 'Reward Settings',
        'conditions' => 'Conditions',
        'metadata' => 'Additional Data',
        'code_details' => 'Code Details',
    ],

    'fields' => [
        'user' => 'User',
        'code' => 'Referral Code',
        'is_active' => 'Active',
        'expires_at' => 'Expires At',
        'title' => 'Title',
        'description' => 'Description',
        'usage_limit' => 'Usage Limit',
        'usage_count' => 'Usage Count',
        'source' => 'Source',
        'tags' => 'Tags',
        'reward_amount' => 'Reward Amount',
        'reward_type' => 'Reward Type',
        'campaign' => 'Campaign',
        'conditions' => 'Conditions',
        'metadata' => 'Additional Data',
        'created_at' => 'Created At',
        'referral_url' => 'Referral URL',
        'usage_percentage' => 'Usage Percentage',
    ],

    'helpers' => [
        'code' => 'Leave empty to auto-generate',
        'expires_at' => 'Leave empty for no expiration',
        'usage_limit' => 'Leave empty for unlimited usage',
        'conditions' => 'JSON format conditions that user must meet',
    ],

    'sources' => [
        'admin' => 'Admin',
        'user' => 'User',
        'api' => 'API',
        'import' => 'Import',
    ],

    'reward_types' => [
        'percentage' => 'Percentage',
        'fixed' => 'Fixed Amount',
        'points' => 'Points',
    ],

    'filters' => [
        'active' => 'Active',
        'expired' => 'Expired',
        'expires_soon' => 'Expires Soon (7 days)',
        'with_usage_limit' => 'With Usage Limit',
        'by_source' => 'By Source',
        'by_reward_type' => 'By Reward Type',
        'select_source' => 'Select Source',
        'select_reward_type' => 'Select Reward Type',
    ],

    'actions' => [
        'deactivate' => 'Deactivate',
        'activate' => 'Activate',
        'deactivate_selected' => 'Deactivate Selected',
        'activate_selected' => 'Activate Selected',
        'copy_url' => 'Copy URL',
        'view_stats' => 'View Statistics',
    ],

    'notifications' => [
        'url_copied' => 'URL copied to clipboard',
    ],

    'stats' => [
        'total_codes' => 'Total Codes',
        'total_codes_description' => 'Total number of referral codes',
        'active_codes' => 'Active Codes',
        'active_codes_description' => 'Currently active codes',
        'expired_codes' => 'Expired Codes',
        'expired_codes_description' => 'Expired or deactivated codes',
        'total_usage' => 'Total Usage',
        'total_usage_description' => 'Total usage count of all codes',
    ],

    'charts' => [
        'usage_over_time' => 'Usage Over Time',
        'usage_label' => 'Usage Count',
    ],

    'widgets' => [
        'top_codes' => 'Top Referral Codes',
    ],

    'unlimited' => 'Unlimited',
    'never_expires' => 'Never expires',
    'no_title' => 'No title',
    'no_description' => 'No description',
    'no_reward' => 'No reward',
    'no_campaign' => 'No campaign',

    'pages' => [
        'index' => [
            'title' => 'My Referral Codes',
            'your_codes' => 'Your Codes',
            'no_codes' => 'No referral codes',
            'no_codes_description' => 'Get started by creating your first referral code.',
        ],
        'create' => [
            'title' => 'Create Referral Code',
        ],
        'edit' => [
            'title' => 'Edit Referral Code',
        ],
        'show' => [
            'title' => 'Referral Code',
        ],
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],

    'messages' => [
        'created_successfully' => 'Referral code created successfully',
        'updated_successfully' => 'Referral code updated successfully',
        'deleted_successfully' => 'Referral code deleted successfully',
        'activated_successfully' => 'Referral code activated successfully',
        'deactivated_successfully' => 'Referral code deactivated successfully',
        'url_copied' => 'URL copied to clipboard',
    ],
];
