<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Http\Controllers\InventoryController;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Tests\TestCase;

final class InventoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure column comparisons honour the low stock threshold while filtering results.
     */
    public function test_index_filters_products_by_low_stock_status(): void
    {
        // Create a shared brand so relationship eager loading has consistent data.
        $brand = Brand::factory()->create();

        // Product that should appear in the low stock listing (quantity above zero but under the threshold).
        $lowStockProduct = Product::factory()
            ->published()
            ->for($brand, 'brand')
            ->create([
                'stock_quantity'      => 3,
                'low_stock_threshold' => 5,
                'manage_stock'        => true,
            ]);

        // Inventory items that should be excluded by the stock status filter.
        Product::factory()
            ->published()
            ->for($brand, 'brand')
            ->create([
                'stock_quantity'      => 20,
                'low_stock_threshold' => 5,
                'manage_stock'        => true,
            ]);

        Product::factory()
            ->published()
            ->for($brand, 'brand')
            ->create([
                'stock_quantity'      => 0,
                'low_stock_threshold' => 5,
                'manage_stock'        => true,
            ]);

        // Perform the request with the low stock filter active via the controller directly to avoid route dependencies.
        $controller = app(InventoryController::class);
        $request = Request::create('/inventory', 'GET', ['stock_status' => 'low_stock']);

        /** @var View $view */
        $view = $controller->index($request);

        /** @var LengthAwarePaginator<int, Product> $products */
        $products = $view->getData()['products'];

        // The paginator should only contain the matching low stock product.
        $this->assertCount(1, $products->items());
        /** @var Product|null $firstProduct */
        $firstProduct = collect($products->items())->first();

        $this->assertNotNull($firstProduct);
        $this->assertSame($lowStockProduct->getKey(), $firstProduct->getKey());
    }

    /**
     * Verify that invalid sort directions are normalised to ascending order.
     */
    public function test_index_sanitises_sort_direction(): void
    {
        // Create products with deterministic names to confirm ordering.
        $brand = Brand::factory()->create();

        $alphaProduct = Product::factory()
            ->published()
            ->for($brand, 'brand')
            ->create(['name' => 'Alpha Item']);

        $omegaProduct = Product::factory()
            ->published()
            ->for($brand, 'brand')
            ->create(['name' => 'Omega Item']);

        // Use an invalid direction to ensure the controller falls back to ascending order.
        $controller = app(InventoryController::class);
        $request = Request::create('/inventory', 'GET', [
            'sort'      => 'name',
            'direction' => 'DROP TABLE',
        ]);

        /** @var View $view */
        $view = $controller->index($request);

        /** @var LengthAwarePaginator<int, Product> $products */
        $products = $view->getData()['products'];

        /** @var \Illuminate\Support\Collection<int, Product> $items */
        $items = collect($products->items());
        $names = $items->pluck('name')->all();

        // Ascending order should place the Alpha product before the Omega product.
        $this->assertSame(['Alpha Item', 'Omega Item'], $names);
        $firstItem = $items->first();
        $lastItem = $items->last();

        $this->assertInstanceOf(Product::class, $firstItem);
        $this->assertInstanceOf(Product::class, $lastItem);
        $this->assertTrue($firstItem->is($alphaProduct));
        $this->assertTrue($lastItem->is($omegaProduct));
    }

    /**
     * Confirm category filtering resolves using the relationship key rather than a raw column name.
     */
    public function test_index_filters_by_category_identifier(): void
    {
        // Set up two categories to verify that only the attached category is returned.
        $matchingCategory = Category::factory()->create();
        $otherCategory = Category::factory()->create();

        $brand = Brand::factory()->create();

        $matchedProduct = Product::factory()
            ->published()
            ->for($brand, 'brand')
            ->create();

        $unmatchedProduct = Product::factory()
            ->published()
            ->for($brand, 'brand')
            ->create();

        // Attach categories using the pivot table helpers provided by Eloquent.
        $matchedProduct->categories()->attach($matchingCategory);
        $unmatchedProduct->categories()->attach($otherCategory);

        // Request the inventory listing filtered to the matching category id via the controller.
        $controller = app(InventoryController::class);
        $request = Request::create('/inventory', 'GET', ['category' => $matchingCategory->getKey()]);

        /** @var View $view */
        $view = $controller->index($request);

        /** @var LengthAwarePaginator<int, Product> $products */
        $products = $view->getData()['products'];

        $this->assertCount(1, $products->items());
        /** @var Product|null $firstProduct */
        $firstProduct = collect($products->items())->first();

        $this->assertNotNull($firstProduct);
        $this->assertSame($matchedProduct->getKey(), $firstProduct->getKey());
        $this->assertTrue($firstProduct->categories->contains($matchingCategory));
    }
}
