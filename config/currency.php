<?php

declare(strict_types=1);

return [
    /*
     * --------------------------------------------------------------------------
     * Zero Decimal Currency Configuration
     * --------------------------------------------------------------------------
     * These settings describe which ISO currency codes should be treated as
     * zero-decimal. The defaults are always applied, while environment and
     * user-specific overrides are only merged when the dedicated feature flag
     * is active. This allows gradual rollout without surprising customers.
     */
    'zero_decimal_currencies' => [
        // Baseline list that remains active regardless of feature toggles.
        'defaults' => ['JPY'],
        // Per-environment overrides that participate when the toggle is enabled.
        'environments' => [
            'local'      => ['JPY', 'KRW'],
            'staging'    => ['JPY', 'KRW'],
            'production' => ['JPY'],
        ],
        // Optional user targeting controls keyed by identifier for fine-grained rollouts.
        'user_targets' => [
            'ids'    => [],
            'emails' => [],
        ],
    ],

    /*
     * --------------------------------------------------------------------------
     * Currency Feature Toggle Defaults
     * --------------------------------------------------------------------------
     * Configuration that guides fallback behaviour when no database-backed
     * feature flag is present. The service honours this metadata to decide
     * whether the override feature should be switched on for a request.
     */
    'features' => [
        'zero_decimal_overrides' => [
            // The canonical key stored in the feature_flags table.
            'key' => 'currency-zero-decimal-overrides',
            // Default enablement before any environment or user overrides.
            'default_enabled' => true,
            // Environment-level override switches used during phased rollout.
            'environments' => [
                'local'      => true,
                'staging'    => true,
                'production' => false,
            ],
            // Percentage used for gradual rollout when no DB flag exists.
            'rollout_percentage' => 50,
        ],
    ],

    /*
     * --------------------------------------------------------------------------
     * Feature Analytics Settings
     * --------------------------------------------------------------------------
     * Contain prefixes and cache controls that keep usage tracking lightweight
     * while still capturing meaningful insight about toggle adoption.
     */
    'analytics' => [
        'event_prefix' => 'feature-toggle',
        'cache_ttl'    => 300,
    ],
];
