<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Product;
use App\Models\ProductFeature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_and_casts_are_configured(): void
    {
        // Instantiate the model without persisting records to inspect configuration.
        $model = new ProductFeature;

        // Validate the mass-assignable attributes guard against unexpected fields.
        self::assertSame([
            'product_id',
            'feature_type',
            'feature_key',
            'feature_value',
            'weight',
            'is_active',
        ], $model->getFillable());

        // Confirm the expected attribute casting is applied for runtime safety.
        self::assertSame([
            'feature_value' => 'string',
            'weight'        => 'decimal:4',
            'is_active'     => 'boolean',
        ], array_intersect_key($model->getCasts(), [
            'feature_value' => null,
            'weight'        => null,
            'is_active'     => null,
        ]));
    }

    public function test_product_relationship_resolves_parent_product(): void
    {
        // Create a product and attach a feature through the factory helper.
        $product = Product::factory()->create();
        $feature = ProductFeature::factory()->for($product)->create();

        // Reload the relationship to assert that the belongsTo association works.
        $feature->unsetRelation('product');
        self::assertTrue($product->is($feature->product));
    }

    public function test_scopes_filter_and_sort_features(): void
    {
        // Seed deterministic features spanning multiple types and values.
        $product = Product::factory()->create();
        $matching = ProductFeature::factory()->for($product)->create([
            'feature_type'  => 'specification',
            'feature_key'   => 'color',
            'feature_value' => '0.8000',
            'weight'        => 0.8,
        ]);
        $other = ProductFeature::factory()->for($product)->create([
            'feature_type'  => 'benefit',
            'feature_key'   => 'durable',
            'feature_value' => '0.2000',
            'weight'        => 0.2,
        ]);

        // Filter by type and feature key to isolate the targeted record.
        $scopedQuery = ProductFeature::query()
            ->byType('specification')
            ->byFeature('color')
            ->withMinValue(0.5);

        self::assertSame($matching->getKey(), $scopedQuery->sole()->getKey());

        // Ensure ordering scopes arrange data as expected for UI consumption.
        $orderedByValue = ProductFeature::query()->orderedByValue()->pluck('feature_value')->all();
        self::assertSame(['0.8000', '0.2000'], $orderedByValue);

        $orderedByName = ProductFeature::query()->orderedByName()->pluck('feature_key')->all();
        self::assertSame(['color', 'durable'], $orderedByName);

        // Ensure the secondary record remains persisted but excluded from the scoped query.
        self::assertSame(2, ProductFeature::query()->count());
        self::assertSame($other->getKey(), ProductFeature::query()->orderedByValue()->get()->last()->getKey());
    }
}
