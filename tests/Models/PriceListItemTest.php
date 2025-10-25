<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class PriceListItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_configuration_defines_expected_fillable_casts_and_translations(): void
    {
        // Instantiate the model without persistence to inspect its guarded configuration.
        $model = new PriceListItem;

        // Ensure the fillable attributes match the contract required for safe mass assignment.
        self::assertSame([
            'price_list_id',
            'product_id',
            'variant_id',
            'price',
            'net_amount',
            'compare_amount',
            'name',
            'description',
            'notes',
            'is_active',
            'is_featured',
            'priority',
            'min_quantity',
            'max_quantity',
            'valid_from',
            'valid_until',
        ], $model->getFillable());

        // Validate that the casts provide the necessary type coercion for numeric and temporal fields.
        $casts = $model->getCasts();
        $expectedCasts = [
            'price'          => 'decimal:2',
            'net_amount'     => 'decimal:4',
            'compare_amount' => 'decimal:4',
            'is_active'      => 'boolean',
            'is_featured'    => 'boolean',
            'priority'       => 'integer',
            'min_quantity'   => 'integer',
            'max_quantity'   => 'integer',
            'valid_from'     => 'datetime',
            'valid_until'    => 'datetime',
        ];

        self::assertSame($expectedCasts, array_intersect_key($casts, $expectedCasts));

        // Translation traits add array casts for multilingual attributes which should remain configured.
        self::assertSame([
            'name'        => 'array',
            'description' => 'array',
            'notes'       => 'array',
        ], array_intersect_key($casts, array_flip(['name', 'description', 'notes'])));

        // Confirm that translations are enabled for the name, description, and notes payloads.
        self::assertSame(['name', 'description', 'notes'], $model->getTranslatableAttributes());
    }

    public function test_relationships_link_to_price_list_product_and_variant(): void
    {
        // Build the relationship instances without hitting the database to validate their configuration.
        $item = new PriceListItem;

        $priceListRelation = $item->priceList();
        self::assertInstanceOf(PriceList::class, $priceListRelation->getRelated());
        self::assertInstanceOf(BelongsTo::class, $priceListRelation);
        self::assertSame('price_list_id', $priceListRelation->getForeignKeyName());

        $productRelation = $item->product();
        self::assertInstanceOf(Product::class, $productRelation->getRelated());
        self::assertSame('product_id', $productRelation->getForeignKeyName());

        $variantRelation = $item->variant();
        self::assertInstanceOf(ProductVariant::class, $variantRelation->getRelated());
        self::assertSame('variant_id', $variantRelation->getForeignKeyName());
    }

    public function test_attribute_accessors_calculate_discount_labels_and_effective_price(): void
    {
        // Freeze time so the active window checks operate deterministically.
        Carbon::setTestNow('2024-05-01 12:00:00');

        // Use the English locale so the translated accessors return the expected language.
        app()->setLocale('en');

        // Create a price list item with clear pricing values and translations to exercise helpers.
        $item = PriceListItem::make([
            'name' => [
                'en' => 'Special Bundle',
                'lt' => 'Specialus Rinkinys',
            ],
            'net_amount'     => 80.0,
            'compare_amount' => 100.0,
            'is_active'      => true,
            'valid_from'     => Carbon::now()->subDay(),
            'valid_until'    => Carbon::now()->addDay(),
            'min_quantity'   => 2,
            'max_quantity'   => 5,
        ]);

        // The translated name accessor should return the localized value and fallback to the base name if missing.
        self::assertSame('Special Bundle', $item->display_name);

        // Discount helpers should reflect the percentage and absolute savings correctly.
        self::assertSame(20, $item->discount_percentage);
        self::assertSame(80.0, $item->effective_price);
        self::assertSame(20.0, $item->savings_amount);
        self::assertSame(80.0, $item->price);

        // Quantity validation should honour the configured thresholds.
        self::assertTrue($item->isValidForQuantity(3));
        self::assertFalse($item->isValidForQuantity(1));
        self::assertFalse($item->isValidForQuantity(6));

        // Active window logic should succeed while the record is inside the configured validity period.
        self::assertTrue($item->isActive());

        // Move outside the validity period to ensure the helper responds appropriately.
        $item->valid_until = Carbon::now()->subMinutes(5);
        self::assertFalse($item->isActive());

        // Reset the mocked time to avoid test leakage.
        Carbon::setTestNow();
    }

    public function test_scopes_filter_and_order_records(): void
    {
        // Make locale-sensitive assertions deterministic for the orderedByName scope.
        app()->setLocale('en');
        Carbon::setTestNow('2024-05-01 12:00:00');

        // Scope: valid should constrain the query to active items within the validity window.
        $validQuery = PriceListItem::query()->valid();
        self::assertStringContainsString('"is_active" = ?', $validQuery->toSql());
        $validBindings = $validQuery->getBindings();
        self::assertCount(4, $validBindings);
        self::assertTrue($validBindings[0]);
        self::assertInstanceOf(Carbon::class, $validBindings[1]);
        self::assertInstanceOf(Carbon::class, $validBindings[2]);
        self::assertTrue($validBindings[3]);

        // Scope: byPriority desc should append an ORDER BY clause with the requested direction.
        $byPriority = PriceListItem::query()->byPriority('desc');
        self::assertStringContainsString('order by "priority" desc', $byPriority->toSql());

        // Scope: orderedByName asc should rely on the shared trait's sanitised ordering clause.
        $orderedAsc = PriceListItem::query()->orderedByName();
        self::assertStringContainsString('order by "name" asc', $orderedAsc->toSql());

        // Scope: orderedByName desc should invert the ordering direction while retaining sanitisation.
        $orderedDesc = PriceListItem::query()->orderedByName('desc');
        self::assertStringContainsString('order by "name" desc', $orderedDesc->toSql());

        // Scope: forProduct and forVariant should add simple equality constraints.
        $forProduct = PriceListItem::query()->forProduct(7);
        self::assertStringContainsString('"product_id" = ?', $forProduct->toSql());
        self::assertStringContainsString('"is_active" = ?', $forProduct->toSql());
        self::assertSame(7, $forProduct->getBindings()[0]);

        $forVariant = PriceListItem::query()->forVariant(11);
        self::assertStringContainsString('"variant_id" = ?', $forVariant->toSql());
        self::assertStringContainsString('"is_active" = ?', $forVariant->toSql());
        self::assertSame(11, $forVariant->getBindings()[0]);

        // Scope: inPriceRange should inject a between clause with the provided bounds.
        $priceRange = PriceListItem::query()->inPriceRange(10, 25);
        self::assertStringContainsString('"net_amount" between ? and ?', $priceRange->toSql());
        self::assertSame(10.0, $priceRange->getBindings()[0]);
        self::assertSame(25.0, $priceRange->getBindings()[1]);

        // Scope: withDiscount should emit a column comparison ensuring compare_amount exceeds net_amount.
        $withDiscount = PriceListItem::query()->withDiscount();
        self::assertStringContainsString('"compare_amount" is not null', $withDiscount->toSql());
        self::assertStringContainsString('"compare_amount" > "net_amount"', $withDiscount->toSql());

        // Reset helpers to avoid side effects on other tests.
        Carbon::setTestNow();
    }
}
