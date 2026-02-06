<?php

declare(strict_types=1);

use Database\Seeders\AdminAuthorizationSeeder;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\AllSeedersSeeder;
use Database\Seeders\AttributeSeeder;
use Database\Seeders\AttributeValueSeeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\CustomerGroupSeeder;
use Database\Seeders\OptimizedFullSeeder;

return [
    /*
     * The default seeder profile is used when no explicit --profile option is supplied.
     * It can be overridden through the DB_SEED_PROFILE environment variable.
     */
    'default_profile' => env('DB_SEED_PROFILE', 'optimized'),

    /*
     * Tables excluded when running `db:seed --clear`.
     */
    'truncate_excluded' => [
        'migrations',
        'failed_jobs',
        'jobs',
        'job_batches',
        'cache',
        'cache_locks',
        'sessions',
        'personal_access_tokens',
    ],

    /*
     * Profiles centralize the exact seeder classes that should be executed.
     * Adjust the lists below to include or exclude fixture sets as needed.
     */
    'profiles' => [
        'minimal' => [
            // Core catalog metadata and admin access essentials.
            CurrencySeeder::class,
            AttributeSeeder::class,
            AttributeValueSeeder::class,
            AdminAuthorizationSeeder::class,
            AdminUserSeeder::class,
            CustomerGroupSeeder::class,
        ],
        'optimized' => [
            // Fast, curated production-like dataset for day-to-day development.
            OptimizedFullSeeder::class,
        ],
        'full' => [
            // Backwards-compatible alias for teams using DB_SEED_PROFILE=full.
            OptimizedFullSeeder::class,
        ],
        'legacy_full' => [
            // Legacy exhaustive mode that executes all discovered seeders.
            AllSeedersSeeder::class,
        ],
    ],
];
