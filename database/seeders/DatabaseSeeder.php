<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\ActivityLogStatus;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database using the configured profile.
     */
    public function run(): void
    {
        /**
         * Resolve the list of configured profiles with a defensive fallback
         * in case the configuration has been modified unexpectedly.
         */
        $profiles = Config::get('seeds.profiles', []);
        $defaultProfile = (string) Config::get('seeds.default_profile', 'full');
        $activeProfile = (string) Config::get('seeds.active_profile', $defaultProfile);

        if (! is_array($profiles) || $profiles === []) {
            // Bail out early with the legacy seeder order if configuration is missing.
            $seeders = [
                CurrencySeeder::class,
                AttributeSeeder::class,
                AttributeValueSeeder::class,
                AdminAuthorizationSeeder::class,
                RolesAndPermissionsSeeder::class,
                AdminUserSeeder::class,
                DemoStoreSeeder::class,
            ];
        } else {
            // Gracefully fall back to the default profile when an invalid name is provided.
            $resolvedProfile = Arr::has($profiles, $activeProfile)
                ? $activeProfile
                : $defaultProfile;

            $seeders = Arr::get($profiles, $resolvedProfile, []);
        }

        /** @var ActivityLogStatus $activityLogStatus */
        $activityLogStatus = app(ActivityLogStatus::class);
        $wasLoggingDisabled = $activityLogStatus->disabled();

        if (! $wasLoggingDisabled) {
            // Temporarily suspend activity logging for cleaner seed runs.
            activity()->disableLogging();
        }

        $this->call([
            // Minimal, usable foundation
            CurrencySeeder::class,
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            SystemUserSeeder::class,
            // Comprehensive admin seeder with all menu items
            AdminSeeder::class,
            // Countries for shipping/tax logic
            CountrySeeder::class,
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
            // Replace placeholder assets with generated WebP images
            RealProductImagesSeeder::class,
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
            // CampaignSeeder::class, // Temporarily disabled due to factory issues
            // Partners & tiers
            PartnerTierSeeder::class,
            PartnerSeeder::class,
            // Customer segmentation: groups and realistic customers/orders distribution
            CustomerSegmentationSeeder::class,
            // High-volume customers for reviews authorship and load testing
            BulkCustomerSeeder::class,
            // Normal settings for /admin/normal-settings CRUD
            NormalSettingSeeder::class,
            NormalSettingTranslationSeeder::class,
            // Comprehensive orders for analytics (current and previous month, with paid statuses)
            // ComprehensiveOrderSeeder::class, // Temporarily disabled due to memory issues
            // Collections for admin/collections CRUD
            CollectionSeeder::class,
            CollectionProductSeeder::class,
            // Product reviews for admin/reviews CRUD
            ReviewsSeeder::class,
            // Reports CRUD samples
            // ReportSeeder::class, // Temporarily disabled due to parameterize error
            // Cart items for admin/cart-items CRUD
            CartItemSeeder::class,
            // Wishlist items for admin/wishlist-items CRUD
            WishlistItemSeeder::class,
            // Variant stock history for admin/variant-stock-histories CRUD
            // VariantStockHistorySeeder::class, // Temporarily disabled due to factory issues
            // Analytics & SEO
            AnalyticsEventSeeder::class,
            SeoDataSeeder::class,
            // News demo content
            NewsSeeder::class,
            NewsTagSeeder::class,
            NewsCategorySeeder::class,
            NewsImageSeeder::class,
            NewsCommentSeeder::class,
            // Legal pages
            LegalSeeder::class,
            // Build header menu from categories
            MenuSeeder::class,
            // Sliders for homepage
            SliderSeeder::class,
            // New admin resources
            CampaignScheduleSeeder::class,
            DocumentTemplateSeeder::class,
            // EnumValueSeeder::class, // File doesn't exist
            // Variant combinations for admin/variant-combinations CRUD
            VariantCombinationSeeder::class,
            // System settings
            SystemSettingCategorySeeder::class,
            SystemSettingSeeder::class,
        ]);

        if (!$wasLoggingDisabled) {
            activity()->enableLogging();
        }
    }
}
