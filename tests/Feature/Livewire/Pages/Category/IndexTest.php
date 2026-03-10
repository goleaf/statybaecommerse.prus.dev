<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Pages\Category;

use App\Livewire\Pages\Category\Index;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_successfully(): void
    {
        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertSet('isIndex', true);
    }

    public function test_localized_categories_route_renders_when_visible_categories_exist(): void
    {
        Category::factory()->create([
            'is_visible' => true,
        ]);

        $response = $this->followingRedirects()->get('/lt/categories');

        $response->assertSuccessful();
    }

    public function test_it_filters_categories_by_selected_brand_checkboxes(): void
    {
        $selectedBrand = Brand::factory()->create();
        $otherBrand = Brand::factory()->create();

        $matchingCategory = Category::factory()->create([
            'name'       => 'Matching Brand Category',
            'is_visible' => true,
        ]);

        $otherCategory = Category::factory()->create([
            'name'       => 'Other Brand Category',
            'is_visible' => true,
        ]);

        $selectedBrandProduct = Product::factory()->create([
            'brand_id' => $selectedBrand->id,
        ]);
        $selectedBrandProduct->categories()->attach($matchingCategory->id);

        $otherBrandProduct = Product::factory()->create([
            'brand_id' => $otherBrand->id,
        ]);
        $otherBrandProduct->categories()->attach($otherCategory->id);

        $component = Livewire::test(Index::class)
            ->set('selectedBrandIds', [$selectedBrand->id]);

        /** @var \Illuminate\Support\Collection<int, array{category: \App\Models\Category, depth: int}> $filteredRows */
        $filteredRows = $component->instance()->categories;
        $filteredIds = $filteredRows
            ->map(static fn (array $row): int => (int) $row['category']->id)
            ->all();

        $this->assertContains($matchingCategory->id, $filteredIds);
        $this->assertNotContains($otherCategory->id, $filteredIds);
    }

    public function test_it_hides_categories_without_in_stock_products_when_in_stock_filter_is_enabled(): void
    {
        $inStockCategory = Category::factory()->create([
            'name'       => 'In Stock Category',
            'is_visible' => true,
        ]);
        $outOfStockCategory = Category::factory()->create([
            'name'       => 'Out Of Stock Category',
            'is_visible' => true,
        ]);

        $inStockProduct = Product::factory()->create([
            'stock_quantity' => 8,
        ]);
        $inStockProduct->categories()->attach($inStockCategory->id);

        $outOfStockProduct = Product::factory()->create([
            'stock_quantity' => 0,
        ]);
        $outOfStockProduct->categories()->attach($outOfStockCategory->id);

        $variantInStockCategory = Category::factory()->create([
            'name'       => 'Variant In Stock Category',
            'is_visible' => true,
        ]);

        $variantBackedProduct = Product::factory()->create([
            'stock_quantity' => 0,
            'manage_stock'   => true,
        ]);
        $variantBackedProduct->categories()->attach($variantInStockCategory->id);

        ProductVariant::factory()->create([
            'product_id'      => $variantBackedProduct->id,
            'stock_quantity'  => 6,
            'track_inventory' => true,
        ]);

        $component = Livewire::test(Index::class)
            ->set('inStock', true);

        /** @var \Illuminate\Support\Collection<int, array{category: \App\Models\Category, depth: int}> $filteredRows */
        $filteredRows = $component->instance()->categories;
        $filteredIds = $filteredRows
            ->map(static fn (array $row): int => (int) $row['category']->id)
            ->all();

        $this->assertContains($inStockCategory->id, $filteredIds);
        $this->assertContains($variantInStockCategory->id, $filteredIds);
        $this->assertNotContains($outOfStockCategory->id, $filteredIds);
    }
}
