<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

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
]);

it('relation methods (if present) return proper Relation subclasses', function (string $class, array $rels) {
    $model = new $class;

    foreach ($rels as [$method, $relationClass]) {
        if (! method_exists($model, $method)) {
            // Method not implemented — this smoke test tolerates that
            continue;
        }

        // Use the helper (throws if the return type is wrong)
        Tests\Support\AssertsRelations::assertRelation($model, $method, $relationClass);
    }

    expect(true)->toBeTrue(); // If we reached here, nothing was invalid.
})->with('relationsMatrix');
