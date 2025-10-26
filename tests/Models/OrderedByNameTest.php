<?php

declare(strict_types=1);

use App\Models\Concerns\OrdersByName;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

// Dataset enumerating models that should opt into the OrdersByName trait along with
// the acceptable column candidates each model may legitimately target.
dataset('orderedByNameModels_2', [
    [App\Models\ActivityLog::class, ['log_name', 'event', 'action']],
    [App\Models\AnalyticsEvent::class, ['event_name', 'event', 'name']],
    [App\Models\ApiKey::class, ['name', 'key']],
    [App\Models\Discount::class, ['name', 'title']],
    [App\Models\DiscountCode::class, ['code', 'name']],
    [App\Models\DiscountCondition::class, ['name', 'title', 'type']],
    [App\Models\Document::class, ['name', 'title', 'filename']],
    [App\Models\DocumentTemplate::class, ['name', 'title', 'key']],
    [App\Models\EmailCampaign::class, ['name', 'title']],
    [App\Models\EmailCampaignRecipient::class, ['email', 'name']],
    [App\Models\Export::class, ['name', 'title', 'filename']],
    [App\Models\FeatureFlag::class, ['name', 'key']],
    [App\Models\Inventory::class, ['sku', 'product_name']],
    [App\Models\Legal::class, ['title', 'name', 'key']],
    [App\Models\Location::class, ['name']],
    [App\Models\NewsApproval::class, ['title', 'status', 'decision']],
    [App\Models\NewsComment::class, ['title', 'author_name']],
    [App\Models\NewsImage::class, ['title', 'alt_text', 'caption']],
    [App\Models\NormalSetting::class, ['key', 'name']],
    [App\Models\NormalSettingTranslation::class, ['display_name', 'name', 'label']],
    [App\Models\Notification::class, ['title', 'subject', 'type']],
    [App\Models\OrderShipping::class, ['service_name', 'carrier_name', 'name']],
    [App\Models\PriceList::class, ['name', 'title']],
    [App\Models\PriceListItem::class, ['name', 'title']],
    [App\Models\ProductComparison::class, ['name', 'title', 'session_id']],
    [App\Models\ProductFeature::class, ['name', 'title', 'feature_key']],
    [App\Models\ProductImage::class, ['title', 'alt_text']],
    [App\Models\ProductRequest::class, ['title', 'subject', 'name']],
    [App\Models\RecommendationCache::class, ['name', 'key', 'cache_key']],
    [App\Models\RecommendationConfig::class, ['name', 'title']],
    [App\Models\RecommendationConfigSimple::class, ['name', 'title']],
    [App\Models\Referral::class, ['name', 'title', 'code']],
    [App\Models\ReferralCampaign::class, ['name', 'title']],
    [App\Models\ReferralCode::class, ['code', 'name']],
    [App\Models\ReferralReward::class, ['name', 'title']],
    [App\Models\Review::class, ['title', 'subject']],
    [App\Models\SeoData::class, ['title', 'slug']],
    [App\Models\Setting::class, ['name', 'key']],
    [App\Models\ShippingOption::class, ['service_name', 'name']],
    [App\Models\Subscriber::class, ['email', 'name']],
    [App\Models\SystemSetting::class, ['name', 'key']],
    [App\Models\SystemSettingCategory::class, ['name', 'title']],
    [App\Models\SystemSettingCategoryTranslation::class, ['name', 'title']],
    [App\Models\SystemSettingDependency::class, ['name', 'key', 'condition']],
    [App\Models\SystemSettingHistory::class, ['name', 'key']],
    [App\Models\SystemSettingTranslation::class, ['name', 'title', 'key']],
    [App\Models\UserBehavior::class, ['event', 'action', 'behavior_type']],
    [App\Models\UserProductInteraction::class, ['event', 'action', 'interaction_type']],
    [App\Models\UserWishlist::class, ['name', 'title']],
    [App\Models\VariantAttributeValue::class, ['value', 'label', 'attribute_value_display']],
    [App\Models\VariantCombination::class, ['name', 'title', 'sku', 'combination_hash']],
    [App\Models\VariantImage::class, ['title', 'alt_text']],
    [App\Models\VariantInventory::class, ['sku', 'name']],
    [App\Models\VariantPricingRule::class, ['name', 'title', 'code']],
]);

it('mixes in OrdersByName with an expected column hint', function (string $modelClass, array $candidates): void {
    // Sanity check that each dataset entry references an Eloquent model.
    expect(is_a($modelClass, Model::class, true))->toBeTrue();

    // Confirm the model (recursively) uses the shared OrdersByName trait.
    $traits = class_uses_recursive($modelClass);
    expect($traits)->toContain(OrdersByName::class);

    // Reflect the configured $nameColumn property when present so we can assert
    // the targeted column belongs to the allowed candidate list without relying
    // on public accessors or leaking implementation details.
    $instance = new $modelClass;
    $nameColumn = 'name';
    $reflection = new ReflectionClass($modelClass);

    if ($reflection->hasProperty('nameColumn')) {
        $property = $reflection->getProperty('nameColumn');
        $property->setAccessible(true);
        $value = $property->getValue($instance);
        $nameColumn = is_string($value) ? $value : (string) $value;
    }

    expect($candidates)
        ->toContain($nameColumn);
})->with('orderedByNameModels_2');
