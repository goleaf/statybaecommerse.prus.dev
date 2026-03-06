<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductVariantResource\Pages\ListProductVariants;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductVariantStockFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        $this->admin = User::factory()->create([
            'email'     => 'info@egisstatyba.lt',
            'is_admin'  => true,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_in_stock_filter_returns_tracked_variants_with_sufficient_quantity(): void
    {
        $variants = $this->createVariantInventorySet();

        Livewire::actingAs($this->admin)
            ->test(ListProductVariants::class)
            ->call('loadTable')
            ->filterTable('stock_status', 'in_stock')
            ->assertCanSeeTableRecords([$variants['inStock']])
            ->assertCanNotSeeTableRecords([
                $variants['lowStock'],
                $variants['outOfStock'],
                $variants['notTracked'],
            ]);
    }

    public function test_low_stock_filter_returns_variants_with_quantity_below_threshold(): void
    {
        $variants = $this->createVariantInventorySet();

        Livewire::actingAs($this->admin)
            ->test(ListProductVariants::class)
            ->call('loadTable')
            ->filterTable('stock_status', 'low_stock')
            ->assertCanSeeTableRecords([$variants['lowStock']])
            ->assertCanNotSeeTableRecords([
                $variants['inStock'],
                $variants['outOfStock'],
                $variants['notTracked'],
            ]);
    }

    public function test_out_of_stock_filter_returns_variants_with_no_available_inventory(): void
    {
        $variants = $this->createVariantInventorySet();

        Livewire::actingAs($this->admin)
            ->test(ListProductVariants::class)
            ->call('loadTable')
            ->filterTable('stock_status', 'out_of_stock')
            ->assertCanSeeTableRecords([$variants['outOfStock']])
            ->assertCanNotSeeTableRecords([
                $variants['inStock'],
                $variants['lowStock'],
                $variants['notTracked'],
            ]);
    }

    public function test_not_tracked_filter_returns_variants_with_inventory_tracking_disabled(): void
    {
        $variants = $this->createVariantInventorySet();

        Livewire::actingAs($this->admin)
            ->test(ListProductVariants::class)
            ->call('loadTable')
            ->filterTable('stock_status', 'not_tracked')
            ->assertCanSeeTableRecords([$variants['notTracked']])
            ->assertCanNotSeeTableRecords([
                $variants['inStock'],
                $variants['lowStock'],
                $variants['outOfStock'],
            ]);
    }

    /**
     * @return array{inStock: ProductVariant, lowStock: ProductVariant, outOfStock: ProductVariant, notTracked: ProductVariant}
     */
    private function createVariantInventorySet(): array
    {
        $product = Product::factory()->create();

        $inStock = $this->createVariantForProduct($product, [
            'track_inventory'     => true,
            'stock_quantity'      => 24,
            'reserved_quantity'   => 4,
            'low_stock_threshold' => 8,
        ]);

        $lowStock = $this->createVariantForProduct($product, [
            'track_inventory'     => true,
            'stock_quantity'      => 12,
            'reserved_quantity'   => 8,
            'low_stock_threshold' => 6,
        ]);

        $outOfStock = $this->createVariantForProduct($product, [
            'track_inventory'     => true,
            'stock_quantity'      => 5,
            'reserved_quantity'   => 5,
            'low_stock_threshold' => 4,
        ]);

        $notTracked = $this->createVariantForProduct($product, [
            'track_inventory'     => false,
            'stock_quantity'      => 18,
            'reserved_quantity'   => 2,
            'low_stock_threshold' => 7,
        ]);

        return [
            'inStock'    => $inStock,
            'lowStock'   => $lowStock,
            'outOfStock' => $outOfStock,
            'notTracked' => $notTracked,
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function createVariantForProduct(Product $product, array $attributes): ProductVariant
    {
        $lowStockThreshold = $attributes['low_stock_threshold'] ?? 0;
        unset($attributes['low_stock_threshold']);

        $variant = ProductVariant::factory()
            ->for($product)
            ->create($attributes);

        $variant->low_stock_threshold = $lowStockThreshold;
        $variant->save();

        return $variant->refresh();
    }
}
