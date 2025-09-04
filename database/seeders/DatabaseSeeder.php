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
            AdminUserSeeder::class,
            LithuanianBuilderShopSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            CollectionSeeder::class,
            UltraFastProductImageSeeder::class,
            DocumentTemplateSeeder::class,
            ComprehensiveOrderSeeder::class,
        ]);
    }
}
