<?php

declare(strict_types=1);

use Database\Seeders\AdminSeeder;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\AllCountriesComprehensiveCitiesSeeder;
use Database\Seeders\AnalyticsEventSeeder;
use Database\Seeders\AttributeSeeder;
use Database\Seeders\AttributeValueSeeder;
use Database\Seeders\BasicFilamentSeeder;
use Database\Seeders\BrandSeeder;
use Database\Seeders\BulkCustomerSeeder;
use Database\Seeders\CampaignScheduleSeeder;
use Database\Seeders\CartItemSeeder;
use Database\Seeders\ChannelSeeder;
use Database\Seeders\CollectionProductSeeder;
use Database\Seeders\CollectionSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\CouponSeeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\CustomerSegmentationSeeder;
use Database\Seeders\DiscountSeeder;
use Database\Seeders\DocumentTemplateSeeder;
use Database\Seeders\LegalSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\NewsCategorySeeder;
use Database\Seeders\NewsCommentSeeder;
use Database\Seeders\NewsImageSeeder;
use Database\Seeders\NewsSeeder;
use Database\Seeders\NewsTagSeeder;
use Database\Seeders\NormalSettingSeeder;
use Database\Seeders\NormalSettingTranslationSeeder;
use Database\Seeders\PartnerSeeder;
use Database\Seeders\PartnerTierSeeder;
use Database\Seeders\ProductHistoryExampleSeeder;
use Database\Seeders\ProductHistorySeeder;
use Database\Seeders\RealProductImagesSeeder;
use Database\Seeders\ReviewsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SeoDataSeeder;
use Database\Seeders\SliderSeeder;
use Database\Seeders\SystemSettingCategorySeeder;
use Database\Seeders\SystemSettingSeeder;
use Database\Seeders\TurboEcommerceSeeder;
use Database\Seeders\VariantCombinationSeeder;
use Database\Seeders\VariantInventorySeeder;
use Database\Seeders\WishlistItemSeeder;
use Database\Seeders\InventorySeeder;

return [
    'default_profile' => env('SEED_PROFILE', 'minimal'),

    'profiles' => [
        'minimal' => [
            CurrencySeeder::class,
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            AdminSeeder::class,
            BasicFilamentSeeder::class,
            CountrySeeder::class,
            ChannelSeeder::class,
            BrandSeeder::class,
            AttributeSeeder::class,
            AttributeValueSeeder::class,
            TurboEcommerceSeeder::class,
            VariantInventorySeeder::class,
            ProductHistoryExampleSeeder::class,
            ProductHistorySeeder::class,
            CouponSeeder::class,
            DiscountSeeder::class,
            PartnerTierSeeder::class,
            PartnerSeeder::class,
            CustomerSegmentationSeeder::class,
            NormalSettingSeeder::class,
            NormalSettingTranslationSeeder::class,
            CollectionSeeder::class,
            CollectionProductSeeder::class,
            ReviewsSeeder::class,
            CartItemSeeder::class,
            WishlistItemSeeder::class,
            SeoDataSeeder::class,
            NewsSeeder::class,
            NewsTagSeeder::class,
            NewsCategorySeeder::class,
            NewsImageSeeder::class,
            NewsCommentSeeder::class,
            LegalSeeder::class,
            MenuSeeder::class,
            SliderSeeder::class,
            CampaignScheduleSeeder::class,
            DocumentTemplateSeeder::class,
            VariantCombinationSeeder::class,
            SystemSettingCategorySeeder::class,
            SystemSettingSeeder::class,
        ],

        'full' => [
            AllCountriesComprehensiveCitiesSeeder::class,
            RealProductImagesSeeder::class,
            LocationSeeder::class,
            InventorySeeder::class,
            BulkCustomerSeeder::class,
            AnalyticsEventSeeder::class,
        ],
    ],
];
