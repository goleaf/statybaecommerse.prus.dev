<?php

declare(strict_types=1);

return [
    // Deprecated audit logging
    'audit_logging' => [
        'feature_removed'     => 'Legacy audit logging has been removed',
        'security_maintained' => 'Security logging continues through Laravel logs',
        'compliance_note'     => 'Compliance requirements are met through standard logging',
    ],

    // General deprecation messages
    'general' => [
        'feature_deprecated'     => 'This feature has been deprecated',
        'backward_compatibility' => 'Backward compatibility is maintained',
        'safe_defaults'          => 'Safe default values are provided',
        'no_data_loss'           => 'No existing data has been lost',
    ],

    // User-facing messages
    'messages' => [
        'functionality_unavailable' => 'This functionality is currently unavailable',
        'feature_being_updated'     => 'This feature is being updated',
        'check_back_later'          => 'Please check back later for updates',
    ],
];
