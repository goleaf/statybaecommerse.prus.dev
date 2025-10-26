<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\ProductVariantResource\Pages\ListProductVariants;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductVariantTabsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        ProductVariant::factory()->create([
            'track_inventory'     => false,
            'available_quantity'  => 0,
            'stock_quantity'      => 0,
            'low_stock_threshold' => 5,
        ]);

        ProductVariant::factory()->create([
            'track_inventory'     => true,
            'available_quantity'  => 10,
            'stock_quantity'      => 12,
            'low_stock_threshold' => 5,
        ]);

        ProductVariant::factory()->create([
            'track_inventory'     => true,
            'available_quantity'  => 2,
            'stock_quantity'      => 4,
            'low_stock_threshold' => 5,
        ]);

        ProductVariant::factory()->create([
            'track_inventory'     => true,
            'available_quantity'  => 0,
            'stock_quantity'      => 3,
            'low_stock_threshold' => 5,
        ]);
    }

    public function test_in_stock_tab_query_works_with_available_quantities(): void
    {
        $page = app(ListProductVariants::class);
        $tabs = $page->getTabs();

        $this->assertSame(3, $tabs['in_stock']->modifyQuery(ProductVariant::query())->count());
    }

    public function test_low_stock_tab_query_uses_available_quantities(): void
    {
        $page = app(ListProductVariants::class);
        $tabs = $page->getTabs();

        $this->assertSame(2, $tabs['low_stock']->modifyQuery(ProductVariant::query())->count());
    }

    public function test_out_of_stock_tab_query_uses_available_quantities(): void
    {
        $page = app(ListProductVariants::class);
        $tabs = $page->getTabs();

        $this->assertSame(1, $tabs['out_of_stock']->modifyQuery(ProductVariant::query())->count());
    }
}
