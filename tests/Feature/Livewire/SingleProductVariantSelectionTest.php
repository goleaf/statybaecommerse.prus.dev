<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\SingleProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SingleProductVariantSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_variant_selection_updates_pricing_and_stock_context(): void
    {
        // Arrange: create a published variable product so storefront flows render variants.
        $product = Product::factory()->create([
            'type'         => 'variable',
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now(),
        ]);

        // Arrange: seed a default variant with comfortable inventory levels for baseline assertions.
        $defaultVariant = ProductVariant::factory()->for($product)->create([
            'price'              => 49.99,
            'compare_price'      => 59.99,
            'is_default_variant' => true,
            'is_default'         => true,
            'track_inventory'    => true,
            'low_stock_threshold' => 5,
        ]);

        // Arrange: seed a secondary variant that should trigger the low stock message when selected.
        $lowStockVariant = ProductVariant::factory()->for($product)->create([
            'price'              => 89.50,
            'compare_price'      => 109.50,
            'is_default_variant' => false,
            'is_default'         => false,
            'track_inventory'    => true,
            'low_stock_threshold' => 5,
        ]);

        // Arrange: attach predictable inventory records so available/reserved quantities have deterministic totals.
        VariantInventory::factory()
            ->for($defaultVariant, 'variant')
            ->state([
                'stock'         => 20,
                'reserved'      => 2,
                'available'     => 18,
                'reorder_point' => 5,
            ])
            ->create();

        VariantInventory::factory()
            ->for($lowStockVariant, 'variant')
            ->state([
                'stock'         => 4,
                'reserved'      => 2,
                'available'     => 2,
                'reorder_point' => 5,
            ])
            ->create();

        // Act: mount the Livewire page component through the testing harness.
        $component = Livewire::test(SingleProduct::class, ['product' => $product]);

        // Assert: the default variant is active with pricing and stock summaries reflecting seeded data.
        $this->assertSame($defaultVariant->id, $component->get('activeVariantId'));
        $this->assertSame([
            'reserved'  => 2,
            'available' => 18,
        ], $component->get('inventorySummary'));
        $this->assertEquals((float) $defaultVariant->price, $component->get('pricingSummary')['current']);
        $this->assertEquals((float) $defaultVariant->compare_price, $component->get('pricingSummary')['compare']);
        $this->assertSame(
            __('product_variants.messages.in_stock', ['quantity' => 18]),
            $component->get('stockMessage')
        );

        // Act: switch to the low stock variant via the same event the storefront dispatches.
        $component->dispatch('variant.selected', variantId: $lowStockVariant->id);

        // Assert: selecting a different variant updates pricing, inventory, and messaging context together.
        $this->assertSame($lowStockVariant->id, $component->get('activeVariantId'));
        $this->assertSame([
            'reserved'  => 2,
            'available' => 2,
        ], $component->get('inventorySummary'));
        $this->assertEquals((float) $lowStockVariant->price, $component->get('pricingSummary')['current']);
        $this->assertEquals((float) $lowStockVariant->compare_price, $component->get('pricingSummary')['compare']);
        $this->assertSame(
            __('product_variants.messages.low_stock', ['quantity' => 2]),
            $component->get('stockMessage')
        );
    }
}
