<?php

declare(strict_types=1);

return [
    'kpis' => [
        'orders_today'                        => 'Orders Today',
        'orders_today_description'            => 'Orders received today',
        'revenue_last_seven_days'             => 'Revenue Last 7 Days',
        'revenue_last_seven_days_description' => 'Total revenue over the last 7 days',
        'new_users_today'                     => 'New Users Today',
        'new_users_today_description'         => 'New registered users today',
        'low_stock_items'                     => 'Low Stock Items',
        'low_stock_items_description'         => 'Items with low stock levels',
    ],
    'actions' => [
        'heading'                  => 'Quick Actions',
        'description'              => 'Frequently used admin actions',
        'rebuild_search'           => 'Rebuild Search Index',
        'rebuild_search_help'      => 'Rebuild the search index for better performance',
        'rebuild_search_heading'   => 'Rebuild Search Index?',
        'rebuild_search_confirm'   => 'This will rebuild the entire search index. This may take several minutes.',
        'clear_cache'              => 'Clear Cache',
        'clear_cache_help'         => 'Clear the application cache',
        'clear_cache_heading'      => 'Clear Cache?',
        'clear_cache_confirm'      => 'This will clear all application cache.',
        'run_minimal_seed'         => 'Run Minimal Seed',
        'run_minimal_seed_help'    => 'Populate database with minimal data',
        'run_minimal_seed_heading' => 'Run Data Seeding?',
        'run_minimal_seed_confirm' => 'This will populate the database with minimal data for testing.',
    ],
    'tables' => [
        'recent_orders'  => 'Recent Orders',
        'low_stock'      => 'Low Stock Items',
        'recent_errors'  => 'Recent Errors',
        'status_unknown' => 'Unknown Status',
        'guest_customer' => 'Guest',
    ],
    'errors' => [
        'metric_unavailable'      => 'Metric unavailable',
        'job'                     => 'Job',
        'queue'                   => 'Queue',
        'connection'              => 'Connection',
        'failed_at'               => 'Failed At',
        'exception'               => 'Exception',
        'retry'                   => 'Retry',
        'retry_placeholder'       => 'Retry not available at this time',
        'no_failures'             => 'No Failures',
        'no_failures_description' => 'There are currently no failed jobs',
    ],
];
