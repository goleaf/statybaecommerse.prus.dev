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
            ShopperCoreSeeder::class,
            ShopperSetupSeeder::class,
            LegalSeeder::class,
            ShopperDemoSeeder::class,
            SuperAdminSeeder::class,
            CustomerSeeder::class,
            GroupSeeder::class,
            PartnerSeeder::class,
            PriceListSeeder::class,
            CampaignSeeder::class,
            DiscountCodeSeeder::class,
            TranslationSeeder::class,
            AdminPresetDiscountsSeeder::class,
            OrderSeeder::class,
            ExtendedDemoSeeder::class,
            EnumDataFixSeeder::class,
            ProductPlaceholdersSeeder::class,
        ]);
    }
}
