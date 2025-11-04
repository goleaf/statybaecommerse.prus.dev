<?php

return [
    'title' => 'Referral Statistics',
    'plural' => 'Referral Statistics',
    'single' => 'Referral Statistic',

    'sections' => [
        'basic_info' => 'Basic Information',
        'basic_info_description' => 'User and date information',
        'referral_stats' => 'Referral Statistics',
        'referral_stats_description' => 'Referral counts and metrics',
        'financial_stats' => 'Financial Statistics',
        'financial_stats_description' => 'Earnings and discounts data',
        'advanced' => 'Advanced Settings',
        'advanced_description' => 'Metadata and additional information',
        'timestamps' => 'Timestamps',
    ],

    'fields' => [
        'user_id' => 'User',
        'user_name' => 'User Name',
        'date' => 'Date',
        'total_referrals' => 'Total Referrals',
        'completed_referrals' => 'Completed Referrals',
        'pending_referrals' => 'Pending Referrals',
        'total_rewards_earned' => 'Total Rewards Earned',
        'total_discounts_given' => 'Total Discounts Given',
        'metadata' => 'Metadata',
        'metadata_key' => 'Metadata Key',
        'metadata_value' => 'Metadata Value',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    'filters' => [
        'user' => 'User',
        'date_range' => 'Date Range',
        'from_date' => 'From Date',
        'until_date' => 'Until Date',
        'has_referrals' => 'Has Referrals',
        'has_rewards' => 'Has Rewards',
    ],

    'actions' => [
        'refresh_stats' => 'Refresh Statistics',
        'refresh_all_stats' => 'Refresh All Statistics',
        'add_metadata' => 'Add Metadata',
    ],

    'notifications' => [
        'stats_refreshed' => 'Statistics refreshed successfully',
        'all_stats_refreshed' => 'All statistics refreshed successfully',
    ],

    'placeholders' => [
        'no_metadata' => 'No metadata',
    ],
];
