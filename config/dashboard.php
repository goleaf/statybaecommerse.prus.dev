<?php

declare(strict_types=1);

return [
    'cache_ttl'        => 60,
    'revenue_statuses' => [
        'completed',
        'delivered',
        'confirmed',
        'processing',
        'shipped',
    ],
    'permissions' => [
        'view_kpis'   => 'dashboard.view_kpis',
        'view_charts' => 'dashboard.view_charts',
        'view_tables' => 'dashboard.view_tables',
        'run_actions' => 'dashboard.run_actions',
    ],
];
