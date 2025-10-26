<?php

declare(strict_types=1);

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountCondition;
use App\Models\DiscountRedemption;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\Inventory;
use App\Models\Notification;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
use App\Models\ProductFeature;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\RecommendationAnalytics;
use App\Models\RecommendationBlock;
use App\Models\RecommendationCache;
use App\Models\RecommendationConfig;
use App\Models\RecommendationConfigSimple;
use App\Models\Referral;
use App\Models\ReferralCampaign;
use App\Models\ReferralCode;
use App\Models\ReferralCodeStatistics;
use App\Models\ReferralCodeUsageLog;
use App\Models\ReferralReward;
use App\Models\ReferralRewardLog;
use App\Models\ReferralStatistics;
use App\Models\ShippingOption;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\SystemSettingCategoryTranslation;
use App\Models\SystemSettingDependency;
use App\Models\SystemSettingHistory;
use App\Models\SystemSettingTranslation;
use App\Models\UiTranslation;
use App\Models\User;
use App\Models\UserBehavior;
use App\Models\UserPreference;
use App\Models\UserProductInteraction;
use App\Models\VariantInventory;
use App\Models\VariantPricingRule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @return list<array{class-string, list<string>}>
 */
dataset('ordered_by_name_models', function (): array {
    return [
        [Coupon::class, ['name', 'code']],
        [Discount::class, ['name']],
        [DiscountCode::class, ['code', 'name']],
        [DiscountCondition::class, ['type', 'name']],
        [EmailCampaign::class, ['name', 'title', 'subject']],
        [EmailCampaignRecipient::class, ['email', 'name']],
        [Inventory::class, ['sku', 'name', 'title']],
        [Notification::class, ['type', 'title', 'subject']],
        [PriceList::class, ['name']],
        [PriceListItem::class, ['name']],
        [ProductFeature::class, ['feature_key', 'name']],
        [ProductImage::class, ['alt_text', 'path']],
        [RecommendationBlock::class, ['name', 'title']],
        [RecommendationCache::class, ['cache_key', 'key', 'name']],
        [RecommendationConfig::class, ['name', 'title']],
        [RecommendationConfigSimple::class, ['name', 'title', 'code']],
        [Referral::class, ['title', 'name']],
        [ReferralCampaign::class, ['name', 'title']],
        [ReferralCode::class, ['code', 'title', 'name']],
        [ReferralReward::class, ['title', 'name']],
        [ShippingOption::class, ['name', 'service_name']],
        [SystemSetting::class, ['key', 'name']],
        [SystemSettingCategory::class, ['name', 'title']],
        [SystemSettingCategoryTranslation::class, ['name', 'title']],
        [SystemSettingDependency::class, ['condition', 'key', 'name']],
        [SystemSettingTranslation::class, ['name', 'key', 'title']],
        [UiTranslation::class, ['key']],
        [UserBehavior::class, ['event', 'name']],
        [UserPreference::class, ['key', 'name', 'preference_key', 'preference_type']],
        [UserProductInteraction::class, ['event', 'action', 'name']],
        [VariantInventory::class, ['sku', 'name']],
        [VariantPricingRule::class, ['name']],
    ];
});

/**
 * @return array<class-string, array<string, class-string>>
 */
dataset('inventory_relations_matrix', function (): array {
    return [
        Inventory::class => [
            'product'   => BelongsTo::class,
            'variant'   => BelongsTo::class,
            'warehouse' => BelongsTo::class,
            'location'  => BelongsTo::class,
            'movements' => HasMany::class,
        ],
    ];
});

/**
 * @return list<array{class-string, non-empty-string, class-string}>
 */
dataset('model_relation_matrix', function (): array {
    return [
        [Discount::class, 'codes', HasMany::class],
        [Discount::class, 'conditions', HasMany::class],
        [Discount::class, 'redemptions', HasMany::class],

        [DiscountCode::class, 'discount', BelongsTo::class],
        [DiscountCode::class, 'redemptions', HasMany::class],
        [DiscountCode::class, 'orders', BelongsToMany::class],
        [DiscountCode::class, 'users', BelongsToMany::class],
        [DiscountCode::class, 'customerGroup', BelongsTo::class],
        [DiscountCode::class, 'documents', MorphMany::class],

        [DiscountCondition::class, 'discount', BelongsTo::class],
        [DiscountCondition::class, 'translations', HasMany::class],
        [DiscountCondition::class, 'products', BelongsToMany::class],
        [DiscountCondition::class, 'categories', BelongsToMany::class],

        [DiscountRedemption::class, 'discount', BelongsTo::class],
        [DiscountRedemption::class, 'code', BelongsTo::class],
        [DiscountRedemption::class, 'user', BelongsTo::class],
        [DiscountRedemption::class, 'order', BelongsTo::class],

        [Coupon::class, 'products', BelongsToMany::class],
        [Coupon::class, 'categories', BelongsToMany::class],
        [Coupon::class, 'customerGroup', BelongsTo::class],
        [Coupon::class, 'orders', HasMany::class],
        [Coupon::class, 'usages', HasMany::class],

        [CouponUsage::class, 'coupon', BelongsTo::class],
        [CouponUsage::class, 'user', BelongsTo::class],
        [CouponUsage::class, 'order', BelongsTo::class],

        [EmailCampaign::class, 'creator', BelongsTo::class],
        [EmailCampaign::class, 'template', BelongsTo::class],
        [EmailCampaign::class, 'recipients', HasMany::class],

        [EmailCampaignRecipient::class, 'campaign', BelongsTo::class],
        [EmailCampaignRecipient::class, 'user', BelongsTo::class],
        [EmailCampaignRecipient::class, 'subscriber', BelongsTo::class],

        [RecommendationBlock::class, 'products', BelongsToMany::class],
        [RecommendationBlock::class, 'analytics', HasMany::class],
        [RecommendationBlock::class, 'caches', HasMany::class],

        [RecommendationCache::class, 'block', BelongsTo::class],
        [RecommendationCache::class, 'user', BelongsTo::class],
        [RecommendationCache::class, 'product', BelongsTo::class],

        [RecommendationConfig::class, 'analytics', HasMany::class],
        [RecommendationConfig::class, 'products', BelongsToMany::class],
        [RecommendationConfig::class, 'categories', BelongsToMany::class],

        [RecommendationConfigSimple::class, 'analytics', HasMany::class],
        [RecommendationConfigSimple::class, 'products', BelongsToMany::class],
        [RecommendationConfigSimple::class, 'categories', BelongsToMany::class],

        [RecommendationAnalytics::class, 'block', BelongsTo::class],
        [RecommendationAnalytics::class, 'config', BelongsTo::class],
        [RecommendationAnalytics::class, 'user', BelongsTo::class],
        [RecommendationAnalytics::class, 'product', BelongsTo::class],

        [Referral::class, 'referrer', BelongsTo::class],
        [Referral::class, 'referred', BelongsTo::class],
        [Referral::class, 'rewards', HasMany::class],
        [Referral::class, 'analyticsEvents', MorphMany::class],
        [Referral::class, 'referredOrders', HasMany::class],
        [Referral::class, 'translations', HasMany::class],
        [Referral::class, 'latestReward', HasOne::class],
        [Referral::class, 'latestReferredOrder', HasOne::class],

        [ReferralCampaign::class, 'referralCodes', HasMany::class],

        [ReferralCode::class, 'user', BelongsTo::class],
        [ReferralCode::class, 'referrals', HasMany::class],
        [ReferralCode::class, 'rewards', HasMany::class],
        [ReferralCode::class, 'campaign', BelongsTo::class],
        [ReferralCode::class, 'usageLogs', HasMany::class],
        [ReferralCode::class, 'statistics', HasMany::class],

        [ReferralCodeStatistics::class, 'referralCode', BelongsTo::class],

        [ReferralCodeUsageLog::class, 'referralCode', BelongsTo::class],
        [ReferralCodeUsageLog::class, 'user', BelongsTo::class],

        [ReferralReward::class, 'referral', BelongsTo::class],
        [ReferralReward::class, 'user', BelongsTo::class],
        [ReferralReward::class, 'order', BelongsTo::class],
        [ReferralReward::class, 'logs', HasMany::class],

        [ReferralRewardLog::class, 'referralReward', BelongsTo::class],
        [ReferralRewardLog::class, 'user', BelongsTo::class],

        [ReferralStatistics::class, 'user', BelongsTo::class],

        [SystemSetting::class, 'category', BelongsTo::class],
        [SystemSetting::class, 'updatedBy', BelongsTo::class],
        [SystemSetting::class, 'translations', HasMany::class],
        [SystemSetting::class, 'history', HasMany::class],
        [SystemSetting::class, 'dependencies', HasMany::class],
        [SystemSetting::class, 'dependents', HasMany::class],

        [SystemSettingCategory::class, 'settings', HasMany::class],
        [SystemSettingCategory::class, 'parent', BelongsTo::class],
        [SystemSettingCategory::class, 'children', HasMany::class],
        [SystemSettingCategory::class, 'translations', HasMany::class],

        [SystemSettingCategoryTranslation::class, 'systemSettingCategory', BelongsTo::class],

        [SystemSettingDependency::class, 'setting', BelongsTo::class],
        [SystemSettingDependency::class, 'dependsOn', BelongsTo::class],

        [SystemSettingHistory::class, 'systemSetting', BelongsTo::class],
        [SystemSettingHistory::class, 'user', BelongsTo::class],

        [SystemSettingTranslation::class, 'systemSetting', BelongsTo::class],

        [Notification::class, 'user', BelongsTo::class],
        [Notification::class, 'notifiable', MorphTo::class],

        [ShippingOption::class, 'orders', HasMany::class],
        [ShippingOption::class, 'country', BelongsTo::class],
        [ShippingOption::class, 'city', BelongsTo::class],
        [ShippingOption::class, 'zone', BelongsTo::class],
    ];
});

/**
 * @return array<class-string, array<string, class-string>>
 */
dataset('user_behavior_relations_matrix', function (): array {
    return [
        UserBehavior::class => [
            'user' => BelongsTo::class,
        ],
    ];
});

/**
 * @return list<array{non-empty-string, class-string, class-string}>
 */
dataset('user_product_interaction_relations_matrix', function (): array {
    return [
        ['user', BelongsTo::class, User::class],
        ['product', BelongsTo::class, Product::class],
        ['variant', BelongsTo::class, ProductVariant::class],
    ];
});
