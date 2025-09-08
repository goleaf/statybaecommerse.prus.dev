<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            ComprehensiveMultilanguageSeeder::class,  // Comprehensive multilanguage seeding
            LithuanianBuilderShopSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            CollectionSeeder::class,
            TranslationSeeder::class,  // Add multilanguage support
            UltraFastProductImageSeeder::class,
            DocumentTemplateSeeder::class,
            ComprehensiveOrderSeeder::class,
            AnalyticsEventSeeder::class,
            EnsureBrandProductsSeeder::class,
        ]);
    }
}
