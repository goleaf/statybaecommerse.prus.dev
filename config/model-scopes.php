<?php

declare(strict_types=1);

use App\Models\Scopes\ActiveCampaignScope;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ApprovedScope;
use App\Models\Scopes\DateRangeScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\PublishedScope;
use App\Models\Scopes\StatusScope;
use App\Models\Scopes\TenantScope;
use App\Models\Scopes\UserOwnedScope;
use App\Models\Scopes\VisibleScope;

return [
    ActiveScope::class => [
        App\Models\Product::class,
        App\Models\Category::class,
        App\Models\Brand::class,
        App\Models\Collection::class,
        App\Models\News::class,
        App\Models\Post::class,
        App\Models\User::class,
        App\Models\Discount::class,
        App\Models\Coupon::class,
        App\Models\FeatureFlag::class,
        App\Models\Subscriber::class,
        App\Models\Partner::class,
        App\Models\Campaign::class,
        App\Models\Attribute::class,
        App\Models\Order::class,
        App\Models\Channel::class,
        App\Models\Zone::class,
        App\Models\Menu::class,
        App\Models\Inventory::class,
        App\Models\VariantInventory::class,
        App\Models\ProductImage::class,
        App\Models\ProductFeature::class,
        App\Models\ProductSimilarity::class,
        App\Models\NewsCategory::class,
        App\Models\NewsTag::class,
        App\Models\NewsComment::class,
        App\Models\NewsImage::class,
        App\Models\AttributeValue::class,
        App\Models\CollectionRule::class,
        App\Models\AdminUser::class,
        App\Models\CampaignClick::class,
        App\Models\CampaignConversion::class,
        App\Models\CampaignCustomerSegment::class,
        App\Models\CampaignProductTarget::class,
        App\Models\CampaignSchedule::class,
        App\Models\CampaignView::class,
        App\Models\City::class,
        App\Models\Country::class,
        App\Models\Currency::class,
        App\Models\CustomerGroup::class,
        App\Models\PartnerTier::class,
        App\Models\DiscountCode::class,
        App\Models\DiscountCondition::class,
        App\Models\DiscountRedemption::class,
        App\Models\Price::class,
        App\Models\PriceList::class,
        App\Models\PriceListItem::class,
        App\Models\Document::class,
        App\Models\DocumentTemplate::class,
        App\Models\Legal::class,
        App\Models\Location::class,
        App\Models\OrderShipping::class,
        App\Models\NotificationTemplate::class,
        App\Models\Setting::class,
        App\Models\SystemSetting::class,
        App\Models\SystemSettingCategory::class,
        App\Models\Company::class,
        App\Models\EmailCampaign::class,
        App\Models\SeoData::class,
        App\Models\Referral::class,
        App\Models\ReferralCampaign::class,
        App\Models\ReferralCode::class,
        App\Models\RecommendationAnalytics::class,
        App\Models\RecommendationBlock::class,
        App\Models\RecommendationCache::class,
        App\Models\ReferralReward::class,
        App\Models\ReferralRewardLog::class,
    ],

    PublishedScope::class => [
        App\Models\Product::class,
        App\Models\News::class,
        App\Models\Post::class,
        App\Models\Legal::class,
    ],

    VisibleScope::class => [
        App\Models\Product::class,
        App\Models\Category::class,
        App\Models\Collection::class,
        App\Models\News::class,
        App\Models\Attribute::class,
        App\Models\MenuItem::class,
        App\Models\NewsComment::class,
    ],

    EnabledScope::class => [
        App\Models\Category::class,
        App\Models\Brand::class,
        App\Models\Discount::class,
        App\Models\FeatureFlag::class,
        App\Models\Partner::class,
        App\Models\Campaign::class,
        App\Models\Attribute::class,
        App\Models\Channel::class,
        App\Models\Zone::class,
        App\Models\ProductVariant::class,
        App\Models\AttributeValue::class,
        App\Models\City::class,
        App\Models\Currency::class,
        App\Models\CustomerGroup::class,
        App\Models\PartnerTier::class,
        App\Models\Price::class,
        App\Models\PriceList::class,
        App\Models\Legal::class,
        App\Models\Location::class,
    ],

    ApprovedScope::class => [
        App\Models\Review::class,
        App\Models\NewsComment::class,
    ],

    StatusScope::class => [
        App\Models\Order::class,
        App\Models\Campaign::class,
        App\Models\Channel::class,
        App\Models\Zone::class,
        App\Models\ProductVariant::class,
        App\Models\VariantInventory::class,
        App\Models\ProductRequest::class,
        App\Models\CampaignConversion::class,
        App\Models\DiscountCode::class,
        App\Models\DiscountRedemption::class,
        App\Models\Document::class,
        App\Models\Referral::class,
        App\Models\ReferralCampaign::class,
        App\Models\ReferralCode::class,
        App\Models\ReferralReward::class,
    ],

    ActiveCampaignScope::class => [
        App\Models\Campaign::class,
    ],

    TenantScope::class => [
        // Models containing tenant identifiers (extend as needed)
    ],

    UserOwnedScope::class => [
        App\Models\CartItem::class,
        App\Models\Address::class,
        App\Models\UserWishlist::class,
        App\Models\WishlistItem::class,
        App\Models\OrderItem::class,
        App\Models\StockMovement::class,
        App\Models\UserBehavior::class,
        App\Models\UserPreference::class,
        App\Models\UserProductInteraction::class,
        App\Models\ProductHistory::class,
        App\Models\ProductComparison::class,
        App\Models\ProductRequest::class,
        App\Models\CouponUsage::class,
        App\Models\AnalyticsEvent::class,
        App\Models\DiscountRedemption::class,
        App\Models\OrderShipping::class,
        App\Models\ReferralCode::class,
        App\Models\RecommendationAnalytics::class,
    ],

    DateRangeScope::class => [
        App\Models\DiscountCode::class,
        App\Models\Price::class,
        App\Models\PriceList::class,
        App\Models\PriceListItem::class,
        App\Models\ReferralCampaign::class,
        App\Models\ReferralCode::class,
    ],
];
