<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class CategoryBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_index_shows_visible_categories_only(): void
    {
        $visibleCategory = Category::factory()->create(['name' => 'Rankiniai įrankiai']);
        $hiddenCategory = Category::factory()->create(['name' => 'Slapta kategorija', 'is_visible' => false]);

        $response = $this->get(route('frontend.categories.index'));

        $response->assertOk()
            ->assertViewIs('categories.index')
            ->assertViewHas('categories', function ($categories) use ($visibleCategory, $hiddenCategory) {
                return $categories->contains('id', $visibleCategory->id)
                    && ! $categories->contains('id', $hiddenCategory->id);
            });
    }

    public function test_category_show_lists_products(): void
    {
        $category = Category::factory()->create(['name' => 'Elektriniai įrankiai']);
        $brand = Brand::factory()->create();

        $product = $this->createPublishedProduct([
            'name' => 'Impaktinis suktuvas',
            'slug' => 'impaktinis-suktuvas',
            'brand_id' => $brand->id,
        ]);
        $product->categories()->attach($category->id);

        $response = $this->get(route('frontend.categories.show', $category));

        $response->assertOk()
            ->assertViewIs('categories.show')
            ->assertViewHas('products', function ($paginator) use ($product) {
                return $paginator->contains('id', $product->id);
            });
    }

    public function test_category_show_returns_404_for_hidden_category(): void
    {
        $category = Category::withoutGlobalScopes()->create([
            'name' => 'Paslėpta kategorija',
            'slug' => 'paslepta-kategorija',
            'is_visible' => false,
        ]);

        $this->get(route('frontend.categories.show', $category))->assertNotFound();
    }

    public function test_category_show_handles_empty_product_list(): void
    {
        $category = Category::factory()->create(['name' => 'Saugos priemonės']);

        $response = $this->get(route('frontend.categories.show', $category));

        $response->assertOk()
            ->assertViewIs('categories.show')
            ->assertViewHas('products', function ($paginator) {
                return $paginator->total() === 0;
            });
    }

    private function createPublishedProduct(array $overrides = []): Product
    {
        return Product::factory()->create(array_merge([
            'status' => 'published',
            'is_visible' => true,
            'is_enabled' => true,
            'published_at' => Carbon::now()->subDay(),
        ], $overrides));
    }
}
