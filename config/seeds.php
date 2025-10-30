<?php

declare(strict_types=1);

use Database\Seeders\AdminAuthorizationSeeder;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\AllSeedersSeeder;
use Database\Seeders\AttributeSeeder;
use Database\Seeders\AttributeValueSeeder;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\CustomerGroupSeeder;
use Database\Seeders\DemoStoreSeeder;
use Database\Seeders\ProductImageSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SliderSeeder;

return [
    /*
     * The default seeder profile is used when no explicit --profile option is supplied.
     * It can be overridden through the DB_SEED_PROFILE environment variable.
     */
    'default_profile' => env('DB_SEED_PROFILE', 'full'),

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
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            CustomerGroupSeeder::class,
        ],
        'full' => [
            // The full profile builds on "minimal" and adds demo storefront content.
            AllSeedersSeeder::class,
        ],
    ],
];
