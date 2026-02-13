<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductVariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_configuration_and_casts(): void
    {
        // Instantiate a new model instance to inspect the mass assignment and casts metadata.
        $model = new ProductVariant;

        // Validate the full list of fillable attributes matches the domain expectations.
        self::assertSame([
            'product_id',
            'sku',
            'name',
            'variant_name_lt',
            'variant_name_en',
            'description_lt',
            'description_en',
            'price',
            'cost_price',
            'wholesale_price',
            'member_price',
            'promotional_price',
            'stock_quantity',
            'reserved_quantity',
            'available_quantity',
            'sold_quantity',
            'weight',
            'track_inventory',
            'is_default',
            'is_default_variant',
            'is_enabled',
            'barcode',
            'attributes',
            'variant_attribute_matrix',
            'is_on_sale',
            'sale_start_date',
            'sale_end_date',
            'is_featured',
            'is_new',
            'is_bestseller',
            'variant_combination_hash',
        ], $model->getFillable());

        // Confirm financial and state attributes use strict casting for reliable arithmetic.
        $casts = $model->getCasts();
        self::assertSame('decimal:4', $casts['price']);
        self::assertSame('decimal:4', $casts['cost_price']);
        self::assertSame('decimal:4', $casts['wholesale_price']);
        self::assertSame('decimal:4', $casts['member_price']);
        self::assertSame('decimal:4', $casts['promotional_price']);
        self::assertSame('decimal:2', $casts['weight']);
        self::assertSame('array', $casts['attributes']);
        self::assertSame('array', $casts['variant_attribute_matrix']);
        self::assertSame('boolean', $casts['track_inventory']);
        self::assertSame('boolean', $casts['is_default']);
        self::assertSame('boolean', $casts['is_default_variant']);
        self::assertSame('boolean', $casts['is_enabled']);
        self::assertSame('boolean', $casts['is_on_sale']);
        self::assertSame('boolean', $casts['is_featured']);
        self::assertSame('boolean', $casts['is_new']);
        self::assertSame('boolean', $casts['is_bestseller']);
    }

    public function test_relationships_are_defined_with_expected_types(): void
    {
        // Instantiate the model and confirm each relationship entry point returns the appropriate relation object.
        $model = new ProductVariant;

        self::assertInstanceOf(BelongsTo::class, $model->product());
        self::assertInstanceOf(BelongsToMany::class, $model->products());
        self::assertInstanceOf(MorphMany::class, $model->prices());
        self::assertInstanceOf(BelongsToMany::class, $model->attributes());
        self::assertInstanceOf(HasMany::class, $model->variantAttributeValues());
        self::assertInstanceOf(HasManyThrough::class, $model->stockMovements());
        self::assertInstanceOf(HasMany::class, $model->inventories());
        self::assertInstanceOf(HasMany::class, $model->orderItems());
        self::assertInstanceOf(HasMany::class, $model->cartItems());
        self::assertInstanceOf(BelongsToMany::class, $model->orders());
        self::assertInstanceOf(HasMany::class, $model->images());
        self::assertInstanceOf(HasOne::class, $model->primaryImage());
        self::assertInstanceOf(HasMany::class, $model->pricingRules());
        self::assertInstanceOf(HasMany::class, $model->similarities());
        self::assertInstanceOf(BelongsToMany::class, $model->discounts());
        self::assertInstanceOf(HasMany::class, $model->variantCombinations());
        self::assertInstanceOf(HasMany::class, $model->translations());
    }

    public function test_scopes_configure_expected_query_constraints(): void
    {
        // Ensure the enabled scope filters on the is_enabled boolean flag.
        $enabledQuery = ProductVariant::query()->enabled();
        $enabledWhere = $enabledQuery->getQuery()->wheres[0];
        self::assertSame('is_enabled', $enabledWhere['column']);
        self::assertSame(true, $enabledWhere['value']);

        // Validate stock scope targets positive quantity values.
        $stockQuery = ProductVariant::query()->inStock();
        $stockWhere = $stockQuery->getQuery()->wheres[0];
        self::assertSame('quantity', $stockWhere['column']);
        self::assertSame('>', $stockWhere['operator']);
        self::assertSame(0, $stockWhere['value']);

        // Confirm status scope binds the provided status string.
        $statusQuery = ProductVariant::query()->byStatus('archived');
        $statusWhere = $statusQuery->getQuery()->wheres[0];
        self::assertSame('status', $statusWhere['column']);
        self::assertSame('archived', $statusWhere['value']);

        // Check the orderedByName scope adds an ascending order clause by default.
        $orderedQuery = ProductVariant::query()->orderedByName();
        $order = $orderedQuery->getQuery()->orders[0];
        self::assertSame('name', $order['column']);
        self::assertSame('asc', strtolower($order['direction']));
    }
}
