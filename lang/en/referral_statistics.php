<?php

declare(strict_types=1);

return [
    'title'    => 'Referral Statistics',
    'plural'   => 'Referral Statistics',
    'single'   => 'Referral Statistic',
    'sections' => [
        'basic_info'                  => 'Basic Information',
        'basic_info_description'      => 'Track who the statistics belong to and when they were captured.',
        'referral_stats'              => 'Referral Performance',
        'referral_stats_description'  => 'Counts for all referral outcomes over the selected period.',
        'financial_stats'             => 'Financial Impact',
        'financial_stats_description' => 'Totals for earned rewards and issued discounts.',
        'advanced'                    => 'Advanced Details',
        'advanced_description'        => 'Store additional metadata about this referral snapshot.',
        'timestamps'                  => 'Timestamps',
    ],
    'fields' => [
        'user_id'               => 'User',
        'user_name'             => 'User',
        'date'                  => 'Date',
        'total_referrals'       => 'Total Referrals',
        'completed_referrals'   => 'Completed Referrals',
        'pending_referrals'     => 'Pending Referrals',
        'total_rewards_earned'  => 'Total Rewards Earned',
        'total_discounts_given' => 'Total Discounts Given',
        'metadata'              => 'Metadata',
        'metadata_key'          => 'Key',
        'metadata_value'        => 'Value',
        'created_at'            => 'Created At',
        'updated_at'            => 'Updated At',
    ],
    'filters' => [
        'user'          => 'User',
        'date_range'    => 'Date Range',
        'from_date'     => 'From Date',
        'until_date'    => 'Until Date',
        'has_referrals' => 'Has Referrals',
        'has_rewards'   => 'Has Rewards',
    ],
    'actions' => [
        'add_metadata'      => 'Add Metadata Entry',
        'refresh_stats'     => 'Refresh Statistics',
        'refresh_all_stats' => 'Refresh All Statistics',
    ],
    'notifications' => [
        'stats_refreshed'     => 'Referral statistics refreshed successfully.',
        'all_stats_refreshed' => 'All referral statistics refreshed successfully.',
    ],
    'placeholders' => [
        'no_metadata' => 'No metadata provided yet.',
    ],
];
