<?php

declare(strict_types=1);

return [
    // Canonical notification categories drive admin filters and API payloads.
    'categories' => [
        'system_notifications' => [
            'label'       => 'System Notifications',
            'description' => 'Admin alerts',
            // Provide aliases so legacy payloads map into the canonical key.
            'aliases' => ['system', 'admin', 'infrastructure'],
        ],
        'user_notifications' => [
            'label'       => 'User Notifications',
            'description' => 'Customer alerts',
            'aliases'     => ['user', 'customer', 'account'],
        ],
        'email_campaigns' => [
            'label'       => 'Email Campaigns',
            'description' => 'Bulk email',
            'aliases'     => ['campaign', 'marketing', 'email'],
        ],
        'newsletter' => [
            'label'       => 'Newsletter',
            'description' => 'Newsletter subscription',
            'aliases'     => ['newsletter', 'subscription'],
        ],
        'order_updates' => [
            'label'       => 'Order Updates',
            'description' => 'Status changes',
            'aliases'     => ['order', 'shipping', 'delivery'],
        ],
        'stock_alerts' => [
            'label'       => 'Stock Alerts',
            'description' => 'Low stock alerts',
            'aliases'     => ['stock', 'inventory', 'low_stock'],
        ],
    ],
];
