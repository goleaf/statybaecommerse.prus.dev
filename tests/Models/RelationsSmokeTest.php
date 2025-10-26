<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AssertsRelations;

uses(RefreshDatabase::class);

// Ensure each relation assertion executes with the full Laravel testing stack.
uses(Tests\TestCase::class);

/**
 * Each dataset row = [FQCN, relations[]]
 * where relations[] = ['methodName', RelationClass].
 * If a method isn't present, we simply continue (no skip, no fail) to keep this smoke test tolerant.
 */
dataset('relationsMatrix', [
    // Batch 2 — expand anytime
    [\App\Models\Address::class, [
        ['customer', BelongsTo::class],
        ['user',     BelongsTo::class],
        ['city',     BelongsTo::class],
        ['country',  BelongsTo::class],
        ['zone',     BelongsTo::class],
        ['orders',   HasMany::class],
    ]],
    [\App\Models\AdminUser::class, [
        ['roles',        BelongsToMany::class],
        ['activityLogs', HasMany::class],
    ]],
    [\App\Models\Attribute::class, [
        ['values',       HasMany::class],
        ['products',     BelongsToMany::class],
        ['variants',     BelongsToMany::class],
    ]],
    [\App\Models\AttributeValue::class, [
        ['attribute',    BelongsTo::class],
        ['products',     BelongsToMany::class],
        ['variants',     BelongsToMany::class],
    ]],
    [\App\Models\Brand::class, [
        ['products',     HasMany::class],
        ['images',       HasMany::class],
    ]],
    [\App\Models\Campaign::class, [
        ['clicks',       HasMany::class],
        ['conversions',  HasMany::class],
        ['schedules',    HasMany::class],
        ['views',        HasMany::class],
        ['products',     BelongsToMany::class],
    ]],
    [\App\Models\CampaignClick::class, [
        ['campaign',     BelongsTo::class],
        ['user',         BelongsTo::class],
        ['product',      BelongsTo::class],
    ]],
    [\App\Models\CampaignConversion::class, [
        ['campaign',     BelongsTo::class],
        ['order',        BelongsTo::class],
        ['user',         BelongsTo::class],
    ]],
    [\App\Models\CampaignConversionTranslation::class, [
        ['campaignConversion', BelongsTo::class],
        ['campaign',           BelongsTo::class],
    ]],
    [\App\Models\CampaignCustomerSegment::class, [
        ['campaign',     BelongsTo::class],
        ['segment',      BelongsTo::class],
        ['customers',    BelongsToMany::class],
    ]],
    [\App\Models\CampaignProductTarget::class, [
        ['campaign',     BelongsTo::class],
        ['product',      BelongsTo::class],
        ['variant',      BelongsTo::class],
    ]],
    [\App\Models\CampaignSchedule::class, [
        ['campaign',     BelongsTo::class],
    ]],
    [\App\Models\CampaignView::class, [
        ['campaign',     BelongsTo::class],
        ['user',         BelongsTo::class],
        ['product',      BelongsTo::class],
    ]],
    [\App\Models\CartItem::class, [
        ['order',        BelongsTo::class],
        ['product',      BelongsTo::class],
        ['variant',      BelongsTo::class],
    ]],
    [\App\Models\Channel::class, [
        ['products',     BelongsToMany::class],
        ['categories',   HasMany::class],
        ['orders',       HasMany::class],
    ]],
    [\App\Models\City::class, [
        ['country',      BelongsTo::class],
        ['zone',         BelongsTo::class],
    ]],
    [\App\Models\Collection::class, [
        ['rules',        HasMany::class],
        ['products',     BelongsToMany::class],
    ]],
    [\App\Models\CollectionRule::class, [
        ['collection',   BelongsTo::class],
    ]],
    [\App\Models\Company::class, [
        ['users',        HasMany::class],
        ['addresses',    HasMany::class],
    ]],
    [\App\Models\ContactMessage::class, [
        ['user',         BelongsTo::class],
    ]],
    [\App\Models\Country::class, [
        ['zones',        HasMany::class],
        ['cities',       HasMany::class],
    ]],
    [\App\Models\Coupon::class, [
        ['orders',       HasMany::class],
        ['usages',       HasMany::class],
    ]],
    // Batch 3 — expanding into discounts, campaigns, referrals, catalog, and system settings
    [\App\Models\Discount::class, [
        ['conditions',     HasMany::class],
        ['codes',          HasMany::class],
        ['redemptions',    HasMany::class],
        ['brands',         BelongsToMany::class],
        ['categories',     BelongsToMany::class],
        ['collections',    BelongsToMany::class],
        ['customers',      BelongsToMany::class],
        ['campaigns',      BelongsToMany::class],
        ['products',       BelongsToMany::class],
        ['customerGroups', BelongsToMany::class],
    ]],
    [\App\Models\DiscountCode::class, [
        ['discount',      BelongsTo::class],
        ['customerGroup', BelongsTo::class],
        ['redemptions',   HasMany::class],
        ['creator',       BelongsTo::class],
        ['updater',       BelongsTo::class],
        ['documents',     MorphMany::class],
        ['orders',        BelongsToMany::class],
        ['users',         BelongsToMany::class],
    ]],
    [\App\Models\DiscountCondition::class, [
        ['discount',     BelongsTo::class],
        ['translations', HasMany::class],
        ['products',     BelongsToMany::class],
        ['categories',   BelongsToMany::class],
    ]],
    [\App\Models\EmailCampaign::class, [
        ['creator',    BelongsTo::class],
        ['template',   BelongsTo::class],
        ['recipients', HasMany::class],
    ]],
    [\App\Models\EmailCampaignRecipient::class, [
        ['campaign',   BelongsTo::class],
        ['user',       BelongsTo::class],
        ['subscriber', BelongsTo::class],
    ]],
    [\App\Models\Notification::class, [
        ['user', BelongsTo::class],
    ]],
    [\App\Models\PriceList::class, [
        ['currency',       BelongsTo::class],
        ['items',          HasMany::class],
        ['customerGroups', BelongsToMany::class],
        ['partners',       BelongsToMany::class],
    ]],
    [\App\Models\Product::class, [
        ['brand',     BelongsTo::class],
        ['categories', BelongsToMany::class],
        ['variants',  HasMany::class],
        ['images',    HasMany::class],
        ['prices',    MorphMany::class],
    ]],
    [\App\Models\ProductVariant::class, [
        ['product',      BelongsTo::class],
        ['prices',       MorphMany::class],
        ['images',       HasMany::class],
        ['inventories',  HasMany::class],
        ['attributes',   BelongsToMany::class],
    ]],
    [\App\Models\Referral::class, [
        ['referrer',             BelongsTo::class],
        ['referred',             BelongsTo::class],
        ['rewards',              HasMany::class],
        ['analyticsEvents',      MorphMany::class],
        ['referredOrders',       HasMany::class],
        ['translations',         HasMany::class],
        ['latestReward',         HasOne::class],
        ['latestReferredOrder',  HasOne::class],
    ]],
    [\App\Models\ReferralCampaign::class, [
        ['referralCodes', HasMany::class],
    ]],
    [\App\Models\ReferralCode::class, [
        ['user',        BelongsTo::class],
        ['referrals',   HasMany::class],
        ['rewards',     HasMany::class],
        ['campaign',    BelongsTo::class],
        ['usageLogs',   HasMany::class],
        ['statistics',  HasMany::class],
    ]],
    [\App\Models\ReferralReward::class, [
        ['referral',      BelongsTo::class],
        ['user',          BelongsTo::class],
        ['order',         BelongsTo::class],
        ['notifications', HasMany::class],
        ['logs',          HasMany::class],
    ]],
    [\App\Models\ShippingOption::class, [
        ['orders',  HasMany::class],
        ['country', BelongsTo::class],
        ['city',    BelongsTo::class],
        ['zone',    BelongsTo::class],
    ]],
    [\App\Models\SystemSetting::class, [
        ['category',      BelongsTo::class],
        ['updatedBy',     BelongsTo::class],
        ['translations',  HasMany::class],
        ['history',       HasMany::class],
        ['dependencies',  HasMany::class],
    ]],
    [\App\Models\SystemSettingCategory::class, [
        ['settings',     HasMany::class],
        ['parent',       BelongsTo::class],
        ['children',     HasMany::class],
        ['translations', HasMany::class],
    ]],
    [\App\Models\SystemSettingCategoryTranslation::class, [
        ['systemSettingCategory', BelongsTo::class],
    ]],
    [\App\Models\SystemSettingDependency::class, [
        ['setting',                  BelongsTo::class],
        ['dependsOnSettingRelation', BelongsTo::class],
    ]],
    [\App\Models\SystemSettingTranslation::class, [
        ['systemSetting', BelongsTo::class],
    ]],
]);

it('relation methods (if present) return proper Relation subclasses', function (string $class, array $rels) {
    $model = new $class;

    foreach ($rels as [$method, $relationClass]) {
        if (! method_exists($model, $method)) {
            continue;
        }

        AssertsRelations::assertRelation($model, $method, $relationClass);
    }

    expect(true)->toBeTrue();
})->with('relationsMatrix');
