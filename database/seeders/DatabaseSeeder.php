<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Activitylog\ActivityLogStatus;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /** @var ActivityLogStatus $activityLogStatus */
        $activityLogStatus = app(ActivityLogStatus::class);
        $wasLoggingDisabled = $activityLogStatus->disabled();

        if (!$wasLoggingDisabled) {
            activity()->disableLogging();
        }

        $this->call([
            // Minimal, usable foundation
            CurrencySeeder::class,
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            // Comprehensive admin seeder with all menu items
            AdminSeeder::class,
            // Countries and zones for shipping/tax logic
            CountrySeeder::class,
            ZoneSeeder::class,
            // RegionSeeder::class, // Regions table is dropped in migration 2025_09_14_204041
            // Comprehensive cities seeding with multilingual support
            AllCountriesComprehensiveCitiesSeeder::class,
            ChannelSeeder::class,
            // Core catalog structure with local images only
            BrandSeeder::class,
            // LithuanianCatalogSeeder::class, // Temporarily disabled due to memory issues
            AttributeSeeder::class,
            AttributeValueSeeder::class,
            // High‑performance product seeding with attributes, relations, translations, and local images
            TurboEcommerceSeeder::class,
            // Enforce max 100 products per brand/category
            EnsureBrandProductsSeeder::class,
            LocationSeeder::class,
            InventorySeeder::class,
            VariantInventorySeeder::class,
            ProductHistoryExampleSeeder::class,
            ProductHistorySeeder::class,
            // Marketing: sample coupons for admin CRUD
            CouponSeeder::class,
            // Discounts for admin/discounts CRUD
            DiscountSeeder::class,
            // Marketing campaigns with localized content
            CampaignSeeder::class,
            // Partners & tiers
            PartnerTierSeeder::class,
            PartnerSeeder::class,
            // Customer segmentation: groups and realistic customers/orders distribution
            CustomerSegmentationSeeder::class,
            // High-volume customers for reviews authorship and load testing
            BulkCustomerSeeder::class,
            // Normal settings for /admin/normal-settings CRUD
            NormalSettingSeeder::class,
            // Comprehensive orders for analytics (current and previous month, with paid statuses)
            // ComprehensiveOrderSeeder::class, // Temporarily disabled due to memory issues
            // Collections for admin/collections CRUD
            CollectionSeeder::class,
            CollectionProductSeeder::class,
            // Product reviews for admin/reviews CRUD
            ReviewsSeeder::class,
            // Reports CRUD samples
            ReportSeeder::class,
            // Cart items for admin/cart-items CRUD
            CartItemSeeder::class,
            // Analytics & SEO
            AnalyticsEventSeeder::class,
            SeoDataSeeder::class,
            // News demo content
            NewsSeeder::class,
            // Legal pages
            LegalSeeder::class,
            // Build header menu from categories
            MenuSeeder::class,
            // Sliders for homepage
            SliderSeeder::class,
        ]);

        if (!$wasLoggingDisabled) {
            activity()->enableLogging();
        }
    }
}
