<?php

declare(strict_types=1);

use Database\Seeders\AdminAuthorizationSeeder;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\AllSeedersSeeder;
use Database\Seeders\AttributeSeeder;
use Database\Seeders\AttributeValueSeeder;
use Database\Seeders\BrochureSeeder;
use Database\Seeders\Cities\CitiesMergedSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\CustomerGroupSeeder;
use Database\Seeders\FeatureFlagSeeder;
use Database\Seeders\InventorySeeder;
use Database\Seeders\LegalSeeder;
use Database\Seeders\NewsSeeder;
use Database\Seeders\OptimizedFullSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\UsersCompanyTabSeeder;
use Database\Seeders\UsersCustomerGroupsTabSeeder;
use Database\Seeders\UsersPartnersTabSeeder;
use Database\Seeders\WarehouseSeeder;

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
     * Global fast-mode switch used by heavy seeders. This is toggled internally by
     * OptimizedFullSeeder so direct seeder runs keep full fixture coverage by default.
     */
    'fast_mode' => false,

    /*
     * Fast-mode knobs for local development seed runs.
     */
    'fast' => [
        'city_iso2' => array_values(array_filter(array_map(
            static fn (string $locale): string => strtoupper(trim($locale)),
            explode(',', (string) env('DB_SEED_FAST_CITY_ISO2', 'LT'))
        ))),
        'max_cities_per_country' => max(1, (int) env('DB_SEED_FAST_MAX_CITIES_PER_COUNTRY', 40)),
        'locales'                => array_values(array_filter(array_map(
            static fn (string $locale): string => strtolower(trim($locale)),
            explode(',', (string) env('DB_SEED_FAST_LOCALES', 'lt,en'))
        ))),
        'generate_media' => (bool) env('DB_SEED_FAST_GENERATE_MEDIA', false),
    ],

    /*
     * Brochure PDF fixture volume for default seed profiles.
     */
    'brochures' => [
        'count'                       => max(1, (int) env('DB_SEED_BROCHURES_COUNT', 12)),
        'files_per_brochure'          => max(1, (int) env('DB_SEED_BROCHURE_FILES_PER_BROCHURE', 4)),
        'inactive_brochure_count'     => max(0, (int) env('DB_SEED_BROCHURES_INACTIVE_COUNT', 3)),
        'inactive_files_per_brochure' => max(0, (int) env('DB_SEED_BROCHURE_INACTIVE_FILES_PER_BROCHURE', 1)),
    ],

    /*
     * Enable fast-mode automatically for the optimized profile.
     */
    'optimized_enables_fast_mode' => (bool) env('DB_SEED_OPTIMIZED_FAST_MODE', true),

    /*
     * Core seeder standard used by the full and legacy profiles.
     */
    'standard_seeders' => [
        CurrencySeeder::class,
        CountrySeeder::class,
        CitiesMergedSeeder::class,
        AdminAuthorizationSeeder::class,
        AdminUserSeeder::class,
        LegalSeeder::class,
        NewsSeeder::class,
        CustomerGroupSeeder::class,
        UsersCompanyTabSeeder::class,
        UsersCustomerGroupsTabSeeder::class,
        UsersPartnersTabSeeder::class,
        ServiceSeeder::class,
        AttributeSeeder::class,
        AttributeValueSeeder::class,
        WarehouseSeeder::class,
        InventorySeeder::class,
        FeatureFlagSeeder::class,
        BrochureSeeder::class,
        SettingsSeeder::class,
    ],

    /*
     * Legacy profile can still discover and run every seeder class when enabled.
     */
    'include_experimental' => (bool) env('DB_SEED_INCLUDE_EXPERIMENTAL', false),

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
            // Legacy entrypoint that follows the same standard list by default.
            AllSeedersSeeder::class,
        ],
    ],
];
