<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Referral System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the referral system.
    | You can customize various aspects of the referral functionality here.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Referral Code Configuration
    |--------------------------------------------------------------------------
    */
    'code_length' => 8,
    'code_min_length' => 6,
    'code_max_length' => 12,
    'code_pattern' => '/^[A-Z0-9]+$/',
    'code_generation_strategy' => 'mixed', // alphanumeric, numeric, mixed

    /*
    |--------------------------------------------------------------------------
    | Referral Rewards Configuration
    |--------------------------------------------------------------------------
    */
    'referred_discount_percentage' => 5.0, // 5% discount for referred users
    'referrer_bonus_amount' => 0.0, // Bonus amount for referrer (0 = disabled)
    'referrer_bonus_currency' => 'EUR',

    /*
    |--------------------------------------------------------------------------
    | Referral Expiration Configuration
    |--------------------------------------------------------------------------
    */
    'referral_expiration_days' => 30, // Days until referral expires
    'reward_expiration_days' => 30, // Days until reward expires
    'referrer_bonus_expiration_days' => 90, // Days until referrer bonus expires

    /*
    |--------------------------------------------------------------------------
    | Referral Limits Configuration
    |--------------------------------------------------------------------------
    */
    'max_referrals_per_user' => 100, // Maximum active referrals per user
    'max_referral_codes_per_user' => 1, // Maximum active codes per user

    /*
    |--------------------------------------------------------------------------
    | Registration Path Configuration
    |--------------------------------------------------------------------------
    */
    'registration_path' => '/register', // Path for registration with referral

    /*
    |--------------------------------------------------------------------------
    | Referral Statistics Configuration
    |--------------------------------------------------------------------------
    */
    'enable_statistics' => true,
    'statistics_retention_days' => 365, // How long to keep statistics

    /*
    |--------------------------------------------------------------------------
    | Referral Notifications Configuration
    |--------------------------------------------------------------------------
    */
    'enable_notifications' => true,
    'notify_on_referral_created' => true,
    'notify_on_referral_completed' => true,
    'notify_on_reward_earned' => true,

    /*
    |--------------------------------------------------------------------------
    | Referral Cleanup Configuration
    |--------------------------------------------------------------------------
    */
    'enable_cleanup' => true,
    'cleanup_frequency_days' => 7, // How often to run cleanup (in days)

    /*
    |--------------------------------------------------------------------------
    | Referral Validation Configuration
    |--------------------------------------------------------------------------
    */
    'validate_referrer_exists' => true,
    'validate_referred_not_already_referred' => true,
    'validate_referral_code_format' => true,
    'validate_referral_code_active' => true,

    /*
    |--------------------------------------------------------------------------
    | Referral Integration Configuration
    |--------------------------------------------------------------------------
    */
    'integrate_with_discount_system' => true,
    'auto_apply_referral_discount' => true,
    'first_order_only' => true, // Only apply discount to first order

    /*
    |--------------------------------------------------------------------------
    | Referral Security Configuration
    |--------------------------------------------------------------------------
    */
    'prevent_self_referral' => true,
    'prevent_duplicate_referrals' => true,
    'rate_limit_referral_creation' => true,
    'rate_limit_per_minute' => 10, // Max referrals per minute per user
];

