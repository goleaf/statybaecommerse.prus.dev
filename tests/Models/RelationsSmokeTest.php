<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Tests\TestCase;

// Boot Laravel so relation assertions can resolve configuration, bindings, and
// other framework helpers required by the instantiated models.
uses(TestCase::class);

// Map each model to the relations we want to sanity check along with the
// acceptable relation classes for flexible polymorphic behaviour.
dataset('relationsMatrix_2', [
    App\Models\ActivityLog::class => [
        'user'    => [BelongsTo::class],
        'subject' => [MorphTo::class],
        'causer'  => [MorphTo::class],
    ],
    App\Models\AnalyticsEvent::class => [
        'user'      => [BelongsTo::class],
        'trackable' => [MorphTo::class],
    ],
    App\Models\ApiKey::class => [
        'user' => [BelongsTo::class],
    ],
    App\Models\Discount::class => [
        'products'       => [BelongsToMany::class],
        'customerGroups' => [BelongsToMany::class],
    ],
    App\Models\DiscountCode::class => [
        'discount'    => [BelongsTo::class],
        'redemptions' => [HasMany::class],
    ],
    App\Models\DiscountCondition::class => [
        'discount'     => [BelongsTo::class],
        'translations' => [HasMany::class],
    ],
    App\Models\Document::class => [
        'template'     => [BelongsTo::class],
        'documentable' => [MorphTo::class],
    ],
    App\Models\DocumentTemplate::class => [
        'documents' => [HasMany::class],
    ],
    App\Models\EmailCampaign::class => [
        'template'   => [BelongsTo::class],
        'recipients' => [HasMany::class],
    ],
    App\Models\EmailCampaignRecipient::class => [
        'campaign' => [BelongsTo::class],
    ],
    App\Models\Export::class => [
        'requestedBy' => [BelongsTo::class],
    ],
    App\Models\FeatureFlag::class => [
        'creator' => [BelongsTo::class],
        'updater' => [BelongsTo::class],
    ],
    App\Models\Inventory::class => [
        'product'  => [BelongsTo::class],
        'location' => [BelongsTo::class],
    ],
    App\Models\Legal::class => [
        'translations' => [HasMany::class],
    ],
    App\Models\Location::class => [
        'country'     => [BelongsTo::class],
        'inventories' => [HasMany::class],
    ],
    App\Models\NewsApproval::class => [
        'news' => [BelongsTo::class],
        'user' => [BelongsTo::class],
    ],
    App\Models\NewsComment::class => [
        'news'    => [BelongsTo::class],
        'parent'  => [BelongsTo::class],
        'replies' => [HasMany::class],
    ],
    App\Models\NewsImage::class => [
        'news' => [BelongsTo::class],
    ],
    App\Models\NormalSetting::class => [
        'translations' => [HasMany::class],
    ],
    App\Models\NormalSettingTranslation::class => [
        'enhancedSetting' => [BelongsTo::class],
    ],
    App\Models\Notification::class => [
        'notifiable' => [MorphTo::class],
    ],
    App\Models\OrderShipping::class => [
        'order' => [BelongsTo::class],
    ],
    App\Models\PriceList::class => [
        'items'          => [HasMany::class],
        'customerGroups' => [BelongsToMany::class],
    ],
    App\Models\PriceListItem::class => [
        'priceList' => [BelongsTo::class],
        'product'   => [BelongsTo::class],
        'variant'   => [BelongsTo::class],
    ],
    App\Models\ProductComparison::class => [
        'user'    => [BelongsTo::class],
        'product' => [BelongsTo::class],
    ],
    App\Models\ProductFeature::class => [
        'product' => [BelongsTo::class],
    ],
    App\Models\ProductImage::class => [
        'product' => [BelongsTo::class],
    ],
    App\Models\ProductRequest::class => [
        'product'     => [BelongsTo::class],
        'user'        => [BelongsTo::class],
        'respondedBy' => [BelongsTo::class],
    ],
    App\Models\RecommendationCache::class => [
        'block'   => [BelongsTo::class],
        'user'    => [BelongsTo::class],
        'product' => [BelongsTo::class],
    ],
    App\Models\RecommendationConfig::class => [
        'analytics' => [HasMany::class],
        'products'  => [BelongsToMany::class],
    ],
    App\Models\RecommendationConfigSimple::class => [
        'analytics' => [HasMany::class],
        'products'  => [BelongsToMany::class],
    ],
    App\Models\Referral::class => [
        'referrer' => [BelongsTo::class],
        'rewards'  => [HasMany::class],
    ],
    App\Models\ReferralCampaign::class => [
        'referralCodes' => [HasMany::class],
    ],
    App\Models\ReferralCode::class => [
        'campaign'  => [BelongsTo::class],
        'referrals' => [HasMany::class],
    ],
    App\Models\ReferralReward::class => [
        'referral' => [BelongsTo::class],
        'user'     => [BelongsTo::class],
    ],
    App\Models\Review::class => [
        'product' => [BelongsTo::class],
        'user'    => [BelongsTo::class],
    ],
    App\Models\SeoData::class => [
        'seoable' => [MorphTo::class],
    ],
    App\Models\Setting::class => [
        // settings often belong to categories through dedicated models
    ],
    App\Models\ShippingOption::class => [
        'country' => [BelongsTo::class],
        'orders'  => [HasMany::class],
    ],
    App\Models\Subscriber::class => [
        'user' => [BelongsTo::class],
    ],
    App\Models\SystemSetting::class => [
        'category'     => [BelongsTo::class],
        'translations' => [HasMany::class],
    ],
    App\Models\SystemSettingCategory::class => [
        'settings' => [HasMany::class],
    ],
    App\Models\SystemSettingCategoryTranslation::class => [
        'category' => [BelongsTo::class],
    ],
    App\Models\SystemSettingDependency::class => [
        'setting'                  => [BelongsTo::class],
        'dependsOnSettingRelation' => [BelongsTo::class],
    ],
    App\Models\SystemSettingHistory::class => [
        'systemSetting' => [BelongsTo::class],
        'user'          => [BelongsTo::class],
    ],
    App\Models\SystemSettingTranslation::class => [
        'systemSetting' => [BelongsTo::class],
    ],
    App\Models\UserBehavior::class => [
        'user'    => [BelongsTo::class],
        'product' => [BelongsTo::class],
    ],
    App\Models\UserProductInteraction::class => [
        'user'    => [BelongsTo::class],
        'product' => [BelongsTo::class],
    ],
    App\Models\UserWishlist::class => [
        'user'  => [BelongsTo::class],
        'items' => [HasMany::class],
    ],
    App\Models\VariantAttributeValue::class => [
        'variant'   => [BelongsTo::class],
        'attribute' => [BelongsTo::class],
    ],
    App\Models\VariantCombination::class => [
        'product' => [BelongsTo::class],
    ],
    App\Models\VariantImage::class => [
        'variant' => [BelongsTo::class],
    ],
    App\Models\VariantInventory::class => [
        'variant'  => [BelongsTo::class],
        'location' => [BelongsTo::class],
    ],
    App\Models\VariantPricingRule::class => [
        'productVariant' => [BelongsTo::class],
        'customerGroup'  => [BelongsTo::class],
    ],
]);

it('exposes the expected relation types', function (string $modelClass, array $relations): void {
    $model = new $modelClass;
    expect($model)->toBeInstanceOf(Model::class);

    foreach ($relations as $method => $allowedTypes) {
        assertRelationOneOf($model, $method, $allowedTypes);
    }
})->with('relationsMatrix_2');

/**
 * Assert that a given relation method returns one of the allowed relation classes.
 */
function assertRelationOneOf(Model $model, string $relation, array $allowedTypes): void
{
    expect(method_exists($model, $relation))->toBeTrue();

    $relationObject = $model->$relation();
    expect($relationObject)->toBeInstanceOf(Relation::class);

    $matches = collect($allowedTypes)->contains(fn (string $class) => $relationObject instanceof $class);
    expect($matches)->toBeTrue();
}
