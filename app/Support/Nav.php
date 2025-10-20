<?php

declare(strict_types=1);

namespace App\Support;

final class Nav
{
    public const NAV_GROUP_ANALYTICS = 'Analytics';
    public const NAV_GROUP_CAMPAIGNS = 'Campaigns';
    public const NAV_GROUP_CONTENT = 'Content';
    public const NAV_GROUP_CONTENT_MANAGEMENT = 'Content Management';
    public const NAV_GROUP_CUSTOMERS = 'Customers';
    public const NAV_GROUP_DISCOUNTS = 'Discounts';
    public const NAV_GROUP_INVENTORY = 'Inventory';
    public const NAV_GROUP_MARKETING = 'Marketing';
    public const NAV_GROUP_NEWS = 'News';
    public const NAV_GROUP_ORDERS = 'Orders';
    public const NAV_GROUP_PRODUCTS = 'Products';
    public const NAV_GROUP_REFERRAL = 'Referral';
    public const NAV_GROUP_REPORTS = 'Reports';
    public const NAV_GROUP_SETTINGS = 'Settings';
    public const NAV_GROUP_SYSTEM = 'System';
    public const NAV_GROUP_USERS = 'Users';

    /**
     * @var array<string, array{icon: string|null, sort: int|null}>
     */
    public const GROUP_DEFINITIONS = [
        self::NAV_GROUP_ANALYTICS => ['icon' => 'heroicon-o-chart-bar', 'sort' => 500],
        self::NAV_GROUP_CAMPAIGNS => ['icon' => 'heroicon-o-rocket-launch', 'sort' => 310],
        self::NAV_GROUP_CONTENT => ['icon' => 'heroicon-o-document-text', 'sort' => 400],
        self::NAV_GROUP_CONTENT_MANAGEMENT => ['icon' => 'heroicon-o-folder', 'sort' => 410],
        self::NAV_GROUP_CUSTOMERS => ['icon' => 'heroicon-o-user-group', 'sort' => 210],
        self::NAV_GROUP_DISCOUNTS => ['icon' => 'heroicon-o-tag', 'sort' => 320],
        self::NAV_GROUP_INVENTORY => ['icon' => 'heroicon-o-archive-box', 'sort' => 110],
        self::NAV_GROUP_MARKETING => ['icon' => 'heroicon-o-megaphone', 'sort' => 300],
        self::NAV_GROUP_NEWS => ['icon' => 'heroicon-o-newspaper', 'sort' => 420],
        self::NAV_GROUP_ORDERS => ['icon' => 'heroicon-o-shopping-bag', 'sort' => 200],
        self::NAV_GROUP_PRODUCTS => ['icon' => 'heroicon-o-cube', 'sort' => 100],
        self::NAV_GROUP_REFERRAL => ['icon' => 'heroicon-o-gift', 'sort' => 330],
        self::NAV_GROUP_REPORTS => ['icon' => 'heroicon-o-document-chart-bar', 'sort' => 510],
        self::NAV_GROUP_SETTINGS => ['icon' => 'heroicon-o-cog-6-tooth', 'sort' => 600],
        self::NAV_GROUP_SYSTEM => ['icon' => 'heroicon-o-cog-6-tooth', 'sort' => 610],
        self::NAV_GROUP_USERS => ['icon' => 'heroicon-o-users', 'sort' => 220],
    ];

    /**
     * @var array<class-string, array{group: string|null, icon: string|null, sort: int|null}>
     */
    public const RESOURCE_CONFIG = [
        \App\Filament\Resources\ActivityLogResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => 'heroicon-o-document-text', 'sort' => 1],
        \App\Filament\Resources\AddressResource::class => ['group' => self::NAV_GROUP_ORDERS, 'icon' => null, 'sort' => 3],
        \App\Filament\Resources\AdminUserResource::class => ['group' => self::NAV_GROUP_USERS, 'icon' => 'heroicon-o-document-text', 'sort' => 1],
        \App\Filament\Resources\AnalyticsEventResource::class => ['group' => self::NAV_GROUP_ANALYTICS, 'icon' => null, 'sort' => 1],
        \App\Filament\Resources\AnalyticsResource::class => ['group' => self::NAV_GROUP_ANALYTICS, 'icon' => 'heroicon-o-chart-bar-square', 'sort' => null],
        \App\Filament\Resources\AttributeResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => 'heroicon-o-tag', 'sort' => null],
        \App\Filament\Resources\AttributeValueResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => 'heroicon-o-tag', 'sort' => 3],
        \App\Filament\Resources\BrandResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\CampaignClickResource::class => ['group' => self::NAV_GROUP_MARKETING, 'icon' => 'heroicon-o-chart-bar', 'sort' => null],
        \App\Filament\Resources\CampaignConversionResource::class => ['group' => self::NAV_GROUP_CAMPAIGNS, 'icon' => 'heroicon-o-rocket-launch', 'sort' => null],
        \App\Filament\Resources\CampaignCustomerSegmentResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\CampaignProductTargetResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\CampaignResource::class => ['group' => self::NAV_GROUP_MARKETING, 'icon' => null, 'sort' => 7],
        \App\Filament\Resources\CampaignScheduleResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\CampaignViewResource::class => ['group' => self::NAV_GROUP_MARKETING, 'icon' => null, 'sort' => 7],
        \App\Filament\Resources\CartItemResource::class => ['group' => null, 'icon' => null, 'sort' => 3],
        \App\Filament\Resources\CategoryResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => 'heroicon-o-tag', 'sort' => null],
        \App\Filament\Resources\ChannelResource::class => ['group' => self::NAV_GROUP_SETTINGS, 'icon' => 'heroicon-o-rectangle-stack', 'sort' => 2],
        \App\Filament\Resources\Channels\ChannelResource::class => ['group' => self::NAV_GROUP_SETTINGS, 'icon' => 'heroicon-o-rectangle-stack', 'sort' => 2],
        \App\Filament\Resources\CityResource::class => ['group' => null, 'icon' => null, 'sort' => 3],
        \App\Filament\Resources\CollectionResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => 'heroicon-o-folder', 'sort' => 2],
        \App\Filament\Resources\CollectionRuleResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => 'heroicon-o-cog-6-tooth', 'sort' => 3],
        \App\Filament\Resources\CompanyResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\CountryResource::class => ['group' => null, 'icon' => null, 'sort' => 1],
        \App\Filament\Resources\Countries\CountryResource::class => ['group' => null, 'icon' => null, 'sort' => 1],
        \App\Filament\Resources\CouponResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\CouponUsageResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\CurrencyResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\CustomerGroupResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\CustomerManagementResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\CustomerResource::class => ['group' => self::NAV_GROUP_USERS, 'icon' => 'heroicon-o-users', 'sort' => 2],
        \App\Filament\Resources\DiscountCodeResource::class => ['group' => self::NAV_GROUP_MARKETING, 'icon' => 'heroicon-o-tag', 'sort' => null],
        \App\Filament\Resources\DiscountConditionResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\DiscountRedemptionResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\DiscountResource::class => ['group' => self::NAV_GROUP_DISCOUNTS, 'icon' => 'heroicon-o-tag', 'sort' => 1],
        \App\Filament\Resources\DocumentResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => 'heroicon-o-document', 'sort' => 20],
        \App\Filament\Resources\DocumentTemplateResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => null, 'sort' => 3],
        \App\Filament\Resources\EmailCampaignResource::class => ['group' => null, 'icon' => 'heroicon-o-envelope', 'sort' => 4],
        \App\Filament\Resources\EmailCampaigns\EmailCampaignResource::class => ['group' => null, 'icon' => 'heroicon-o-envelope', 'sort' => 4],
        \App\Filament\Resources\EnumManagementResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\EnumValueResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => null, 'sort' => 1],
        \App\Filament\Resources\FeatureFlagResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => null, 'sort' => 5],
        \App\Filament\Resources\FeatureFlags\FeatureFlagResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => null, 'sort' => 5],
        \App\Filament\Resources\LegalResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\LocationResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\MenuItemResource::class => ['group' => self::NAV_GROUP_CONTENT, 'icon' => 'heroicon-o-rectangle-stack', 'sort' => 5],
        \App\Filament\Resources\MenuItems\MenuItemResource::class => ['group' => self::NAV_GROUP_CONTENT, 'icon' => 'heroicon-o-rectangle-stack', 'sort' => 5],
        \App\Filament\Resources\MenuResource::class => ['group' => self::NAV_GROUP_CONTENT, 'icon' => 'heroicon-o-rectangle-stack', 'sort' => null],
        \App\Filament\Resources\NewsCategoryResource::class => ['group' => self::NAV_GROUP_CONTENT, 'icon' => 'heroicon-o-tag', 'sort' => 2],
        \App\Filament\Resources\NewsCategories\NewsCategoryResource::class => ['group' => self::NAV_GROUP_CONTENT, 'icon' => 'heroicon-o-tag', 'sort' => 2],
        \App\Filament\Resources\NewsCommentResource::class => ['group' => self::NAV_GROUP_CONTENT, 'icon' => null, 'sort' => 3],
        \App\Filament\Resources\NewsComments\NewsCommentResource::class => ['group' => self::NAV_GROUP_CONTENT, 'icon' => null, 'sort' => 3],
        \App\Filament\Resources\NewsImageResource::class => ['group' => self::NAV_GROUP_CONTENT, 'icon' => 'heroicon-o-photo', 'sort' => 4],
        \App\Filament\Resources\NewsImages\NewsImageResource::class => ['group' => self::NAV_GROUP_CONTENT, 'icon' => 'heroicon-o-photo', 'sort' => 4],
        \App\Filament\Resources\NewsResource::class => ['group' => null, 'icon' => 'heroicon-o-newspaper', 'sort' => 1],
        \App\Filament\Resources\NewsTagResource::class => ['group' => self::NAV_GROUP_NEWS, 'icon' => 'heroicon-o-tag', 'sort' => 4],
        \App\Filament\Resources\NewsTags\NewsTagResource::class => ['group' => self::NAV_GROUP_NEWS, 'icon' => 'heroicon-o-tag', 'sort' => 4],
        \App\Filament\Resources\NormalSettingResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => null, 'sort' => 8],
        \App\Filament\Resources\NormalSettingTranslationResource::class => ['group' => self::NAV_GROUP_CONTENT, 'icon' => null, 'sort' => 16],
        \App\Filament\Resources\NotificationResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => 'heroicon-o-bell', 'sort' => 3],
        \App\Filament\Resources\NotificationTemplateResource::class => ['group' => self::NAV_GROUP_CONTENT, 'icon' => null, 'sort' => 6],
        \App\Filament\Resources\NotificationTemplates\NotificationTemplateResource::class => ['group' => self::NAV_GROUP_CONTENT, 'icon' => null, 'sort' => 6],
        \App\Filament\Resources\OrderItemResource::class => ['group' => self::NAV_GROUP_ORDERS, 'icon' => null, 'sort' => 2],
        \App\Filament\Resources\OrderResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => 'heroicon-o-shopping-bag', 'sort' => 1],
        \App\Filament\Resources\OrderShippingResource::class => ['group' => self::NAV_GROUP_ORDERS, 'icon' => 'heroicon-o-rectangle-stack', 'sort' => 3],
        \App\Filament\Resources\OrderShippings\OrderShippingResource::class => ['group' => self::NAV_GROUP_ORDERS, 'icon' => 'heroicon-o-rectangle-stack', 'sort' => 3],
        \App\Filament\Resources\PartnerResource::class => ['group' => self::NAV_GROUP_MARKETING, 'icon' => 'heroicon-o-user-group', 'sort' => 1],
        \App\Filament\Resources\PartnerTierResource::class => ['group' => self::NAV_GROUP_MARKETING, 'icon' => 'heroicon-o-star', 'sort' => 2],
        \App\Filament\Resources\PostResource::class => ['group' => self::NAV_GROUP_CONTENT, 'icon' => 'heroicon-o-document-text', 'sort' => 2],
        \App\Filament\Resources\PriceListItemResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => 'heroicon-o-currency-euro', 'sort' => 16],
        \App\Filament\Resources\PriceListResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => null, 'sort' => 15],
        \App\Filament\Resources\PriceResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => null, 'sort' => 12],
        \App\Filament\Resources\ProductComparisonResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => null, 'sort' => 15],
        \App\Filament\Resources\ProductFeatureResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => 'heroicon-o-star', 'sort' => 17],
        \App\Filament\Resources\ProductHistoryResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => 'heroicon-o-clock', 'sort' => 11],
        \App\Filament\Resources\ProductImageResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => 'heroicon-o-photo', 'sort' => 14],
        \App\Filament\Resources\ProductRequestResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => 'heroicon-o-clipboard-document-list', 'sort' => 16],
        \App\Filament\Resources\ProductResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => 'heroicon-o-cube', 'sort' => 1],
        \App\Filament\Resources\ProductSimilarityResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\ProductSimilarities\ProductSimilarityResource::class => ['group' => null, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\ProductVariantResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => 'heroicon-o-squares-2x2', 'sort' => 3],
        \App\Filament\Resources\RecommendationAnalyticsResource::class => ['group' => self::NAV_GROUP_ANALYTICS, 'icon' => 'heroicon-o-chart-bar', 'sort' => 8],
        \App\Filament\Resources\RecommendationAnalytics\RecommendationAnalyticsResource::class => ['group' => self::NAV_GROUP_ANALYTICS, 'icon' => 'heroicon-o-chart-bar', 'sort' => 8],
        \App\Filament\Resources\RecommendationBlockResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => null, 'sort' => 13],
        \App\Filament\Resources\RecommendationCacheResource::class => ['group' => self::NAV_GROUP_ANALYTICS, 'icon' => null, 'sort' => 20],
        \App\Filament\Resources\RecommendationCaches\RecommendationCacheResource::class => ['group' => self::NAV_GROUP_ANALYTICS, 'icon' => null, 'sort' => 20],
        \App\Filament\Resources\RecommendationConfigResource::class => ['group' => self::NAV_GROUP_ANALYTICS, 'icon' => null, 'sort' => 11],
        \App\Filament\Resources\ReferralCampaignResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => 'heroicon-o-megaphone', 'sort' => 14],
        \App\Filament\Resources\ReferralCodeResource::class => ['group' => self::NAV_GROUP_REFERRAL, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\ReferralCodeStatisticsResource::class => ['group' => self::NAV_GROUP_ANALYTICS, 'icon' => null, 'sort' => 9],
        \App\Filament\Resources\ReferralCodeStatistics\ReferralCodeStatisticsResource::class => ['group' => self::NAV_GROUP_ANALYTICS, 'icon' => null, 'sort' => 9],
        \App\Filament\Resources\ReferralCodeUsageLogResource::class => ['group' => self::NAV_GROUP_ANALYTICS, 'icon' => null, 'sort' => 18],
        \App\Filament\Resources\ReferralCodeUsageLogs\ReferralCodeUsageLogResource::class => ['group' => self::NAV_GROUP_ANALYTICS, 'icon' => null, 'sort' => 18],
        \App\Filament\Resources\ReferralResource::class => ['group' => self::NAV_GROUP_MARKETING, 'icon' => 'heroicon-o-share', 'sort' => 17],
        \App\Filament\Resources\ReferralRewardLogResource::class => ['group' => self::NAV_GROUP_ANALYTICS, 'icon' => null, 'sort' => 10],
        \App\Filament\Resources\ReferralRewardLogs\ReferralRewardLogResource::class => ['group' => self::NAV_GROUP_ANALYTICS, 'icon' => null, 'sort' => 10],
        \App\Filament\Resources\ReferralRewardResource::class => ['group' => self::NAV_GROUP_REFERRAL, 'icon' => 'heroicon-o-gift', 'sort' => 15],
        \App\Filament\Resources\ReferralStatisticsResource::class => ['group' => self::NAV_GROUP_REFERRAL, 'icon' => 'heroicon-o-chart-bar-square', 'sort' => 14],
        \App\Filament\Resources\ReferralStatistics\ReferralStatisticsResource::class => ['group' => self::NAV_GROUP_REFERRAL, 'icon' => 'heroicon-o-chart-bar-square', 'sort' => 14],
        \App\Filament\Resources\ReferralStatistics\ReferralStatistics\ReferralStatisticsResource::class => ['group' => self::NAV_GROUP_REFERRAL, 'icon' => 'heroicon-o-chart-bar-square', 'sort' => 14],
        \App\Filament\Resources\ReportResource::class => ['group' => self::NAV_GROUP_REPORTS, 'icon' => 'heroicon-o-document-chart-bar', 'sort' => 17],
        \App\Filament\Resources\ReviewResource::class => ['group' => self::NAV_GROUP_CONTENT_MANAGEMENT, 'icon' => 'heroicon-o-star', 'sort' => 4],
        \App\Filament\Resources\SeoDataResource::class => ['group' => self::NAV_GROUP_CONTENT, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\ShippingOptionResource::class => ['group' => self::NAV_GROUP_SETTINGS, 'icon' => 'heroicon-o-truck', 'sort' => 3],
        \App\Filament\Resources\ShippingOptions\ShippingOptionResource::class => ['group' => self::NAV_GROUP_SETTINGS, 'icon' => 'heroicon-o-truck', 'sort' => 3],
        \App\Filament\Resources\SliderResource::class => ['group' => self::NAV_GROUP_CONTENT_MANAGEMENT, 'icon' => 'heroicon-o-rectangle-stack', 'sort' => 4],
        \App\Filament\Resources\Sliders\SliderResource::class => ['group' => self::NAV_GROUP_CONTENT, 'icon' => 'heroicon-o-rectangle-stack', 'sort' => 4],
        \App\Filament\Resources\SliderTranslationResource::class => ['group' => self::NAV_GROUP_CONTENT_MANAGEMENT, 'icon' => 'heroicon-o-rectangle-stack', 'sort' => 3],
        \App\Filament\Resources\StockMovementResource::class => ['group' => self::NAV_GROUP_INVENTORY, 'icon' => 'heroicon-o-archive-box', 'sort' => 3],
        \App\Filament\Resources\StockResource::class => ['group' => self::NAV_GROUP_INVENTORY, 'icon' => null, 'sort' => 7],
        \App\Filament\Resources\SubscriberResource::class => ['group' => self::NAV_GROUP_USERS, 'icon' => null, 'sort' => 1],
        \App\Filament\Resources\SystemResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => 'heroicon-o-cog-6-tooth', 'sort' => 1],
        \App\Filament\Resources\SystemSettingCategoryResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => null, 'sort' => 2],
        \App\Filament\Resources\SystemSettingCategories\SystemSettingCategoryResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => null, 'sort' => 2],
        \App\Filament\Resources\SystemSettingCategoryTranslationResource::class => ['group' => self::NAV_GROUP_SETTINGS, 'icon' => 'heroicon-o-language', 'sort' => 15],
        \App\Filament\Resources\SystemSettingDependencyResource::class => ['group' => self::NAV_GROUP_SETTINGS, 'icon' => 'heroicon-o-link', 'sort' => null],
        \App\Filament\Resources\SystemSettingDependencies\SystemSettingDependencyResource::class => ['group' => self::NAV_GROUP_SETTINGS, 'icon' => 'heroicon-o-link', 'sort' => null],
        \App\Filament\Resources\SystemSettingHistoryResource::class => ['group' => self::NAV_GROUP_SETTINGS, 'icon' => 'heroicon-o-clock', 'sort' => 13],
        \App\Filament\Resources\SystemSettingHistories\SystemSettingHistoryResource::class => ['group' => self::NAV_GROUP_SETTINGS, 'icon' => 'heroicon-o-clock', 'sort' => 13],
        \App\Filament\Resources\SystemSettingResource::class => ['group' => self::NAV_GROUP_SETTINGS, 'icon' => 'heroicon-o-cog-6-tooth', 'sort' => 18],
        \App\Filament\Resources\SystemSettingTranslationResource::class => ['group' => self::NAV_GROUP_SETTINGS, 'icon' => 'heroicon-o-document-text', 'sort' => 14],
        \App\Filament\Resources\Settings\SettingResource::class => ['group' => self::NAV_GROUP_SETTINGS, 'icon' => 'heroicon-o-rectangle-stack', 'sort' => null],
        \App\Filament\Resources\UserBehaviorResource::class => ['group' => self::NAV_GROUP_USERS, 'icon' => 'heroicon-o-document-text', 'sort' => 5],
        \App\Filament\Resources\UserPreferenceResource::class => ['group' => self::NAV_GROUP_USERS, 'icon' => 'heroicon-o-document-text', 'sort' => 6],
        \App\Filament\Resources\UserProductInteractionResource::class => ['group' => self::NAV_GROUP_USERS, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\UserProductInteractions\UserProductInteractionResource::class => ['group' => self::NAV_GROUP_USERS, 'icon' => null, 'sort' => null],
        \App\Filament\Resources\UserResource::class => ['group' => self::NAV_GROUP_USERS, 'icon' => null, 'sort' => 1],
        \App\Filament\Resources\UserWishlistResource::class => ['group' => self::NAV_GROUP_USERS, 'icon' => 'heroicon-o-heart', 'sort' => 8],
        \App\Filament\Resources\VariantAnalyticsResource::class => ['group' => self::NAV_GROUP_INVENTORY, 'icon' => 'heroicon-o-chart-bar-square', 'sort' => 2],
        \App\Filament\Resources\VariantAttributeValueResource::class => ['group' => self::NAV_GROUP_INVENTORY, 'icon' => 'heroicon-o-tag', 'sort' => 18],
        \App\Filament\Resources\VariantCombinationResource::class => ['group' => self::NAV_GROUP_INVENTORY, 'icon' => 'heroicon-o-squares-2x2', 'sort' => 4],
        \App\Filament\Resources\VariantImageResource::class => ['group' => self::NAV_GROUP_INVENTORY, 'icon' => 'heroicon-o-photo', 'sort' => 15],
        \App\Filament\Resources\VariantInventoryResource::class => ['group' => self::NAV_GROUP_INVENTORY, 'icon' => 'heroicon-o-archive-box', 'sort' => 2],
        \App\Filament\Resources\VariantPriceHistoryResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => 'heroicon-o-currency-euro', 'sort' => 20],
        \App\Filament\Resources\VariantPricingRuleResource::class => ['group' => self::NAV_GROUP_PRODUCTS, 'icon' => null, 'sort' => 10],
        \App\Filament\Resources\VariantStockHistoryResource::class => ['group' => self::NAV_GROUP_SYSTEM, 'icon' => null, 'sort' => 3],
        \App\Filament\Resources\VariantStockResource::class => ['group' => self::NAV_GROUP_INVENTORY, 'icon' => 'heroicon-o-archive-box', 'sort' => null],
        \App\Filament\Resources\WishlistItemResource::class => ['group' => self::NAV_GROUP_CUSTOMERS, 'icon' => 'heroicon-o-heart', 'sort' => 10],
    ];

    public static function groupKeyForResource(string $resource): ?string
    {
        return self::RESOURCE_CONFIG[$resource]['group'] ?? null;
    }

    public static function groupForResource(string $resource): ?string
    {
        $group = self::groupKeyForResource($resource);

        return $group !== null ? __($group) : null;
    }

    public static function iconForResource(string $resource): ?string
    {
        return self::RESOURCE_CONFIG[$resource]['icon'] ?? null;
    }

    public static function sortForResource(string $resource): ?int
    {
        return self::RESOURCE_CONFIG[$resource]['sort'] ?? null;
    }

    public static function groupIcon(?string $group): ?string
    {
        return $group !== null ? self::GROUP_DEFINITIONS[$group]['icon'] ?? null : null;
    }

    public static function groupSort(?string $group): ?int
    {
        return $group !== null ? self::GROUP_DEFINITIONS[$group]['sort'] ?? null : null;
    }

    /**
     * @return array<int, array{key: string, label: string, icon: string|null, sort: int|null}>
     */
    public static function navigationGroups(): array
    {
        $groups = [];

        foreach (self::GROUP_DEFINITIONS as $key => $definition) {
            $groups[] = [
                'key' => $key,
                'label' => __($key),
                'icon' => $definition['icon'],
                'sort' => $definition['sort'],
            ];
        }

        usort($groups, static fn (array $a, array $b): int => ($a['sort'] ?? PHP_INT_MAX) <=> ($b['sort'] ?? PHP_INT_MAX));

        return $groups;
    }

    /**
     * @return array<int, class-string>
     */
    public static function orderedResources(): array
    {
        $resources = array_filter(
            self::RESOURCE_CONFIG,
            static function (array $config, string $class): bool {
                $relative = substr($class, strlen('App\\Filament\\Resources\\'));

                return $relative !== false && ! str_contains($relative, '\\');
            },
            ARRAY_FILTER_USE_BOTH
        );

        uksort($resources, static function (string $a, string $b) use ($resources): int {
            $configA = $resources[$a];
            $configB = $resources[$b];

            $groupSortA = self::groupSort($configA['group'] ?? null) ?? PHP_INT_MAX;
            $groupSortB = self::groupSort($configB['group'] ?? null) ?? PHP_INT_MAX;

            if ($groupSortA !== $groupSortB) {
                return $groupSortA <=> $groupSortB;
            }

            $sortA = $configA['sort'] ?? PHP_INT_MAX;
            $sortB = $configB['sort'] ?? PHP_INT_MAX;

            if ($sortA !== $sortB) {
                return $sortA <=> $sortB;
            }

            return $a <=> $b;
        });

        return array_keys($resources);
    }
}
