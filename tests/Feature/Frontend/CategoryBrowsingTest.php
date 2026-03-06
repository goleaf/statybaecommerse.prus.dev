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
            ->assertViewIs('frontend.categories.index')
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
            'name'     => 'Impaktinis suktuvas',
            'slug'     => 'impaktinis-suktuvas',
            'brand_id' => $brand->id,
        ]);
        $product->categories()->attach($category->id);

        $response = $this->get(route('frontend.categories.show', $category));

        $response->assertOk()
            ->assertViewIs('frontend.categories.show')
            ->assertViewHas('products', function ($paginator) use ($product) {
                return $paginator->contains('id', $product->id);
            });
    }

    public function test_category_show_returns_404_for_hidden_category(): void
    {
        $category = Category::withoutGlobalScopes()->create([
            'name'       => 'Paslėpta kategorija',
            'slug'       => 'paslepta-kategorija',
            'is_visible' => false,
        ]);

        $this->get(route('frontend.categories.show', $category))->assertNotFound();
    }

    public function test_category_show_handles_empty_product_list(): void
    {
        $category = Category::factory()->create(['name' => 'Saugos priemonės']);

        $response = $this->get(route('frontend.categories.show', $category));

        $response->assertOk()
            ->assertViewIs('frontend.categories.show')
            ->assertViewHas('products', function ($paginator) {
                return $paginator->total() === 0;
            });
    }

    public function test_category_show_includes_published_product_counts_for_related_categories(): void
    {
        $parent = Category::factory()->create(['name' => 'Drabužiai']);
        $category = Category::factory()->create([
            'name'      => 'Pirštinės',
            'parent_id' => $parent->id,
        ]);
        $relatedA = Category::factory()->create([
            'name'      => 'Marškinėliai',
            'parent_id' => $parent->id,
        ]);
        $relatedB = Category::factory()->create([
            'name'      => 'Kelnės',
            'parent_id' => $parent->id,
        ]);
        $brand = Brand::factory()->create();

        $firstProduct = $this->createPublishedProduct(['brand_id' => $brand->id]);
        $firstProduct->categories()->attach($relatedA->id);

        $secondProduct = $this->createPublishedProduct(['brand_id' => $brand->id]);
        $secondProduct->categories()->attach($relatedB->id);

        $response = $this->get(route('frontend.categories.show', $category));

        $response->assertOk()
            ->assertViewHas('relatedCategories', function ($relatedCategories) use ($relatedA, $relatedB) {
                $relatedById = $relatedCategories->keyBy('id');

                return (int) ($relatedById->get($relatedA->id)?->published_products_count ?? 0) > 0
                    && (int) ($relatedById->get($relatedB->id)?->published_products_count ?? 0) > 0;
            });
    }

    public function test_category_show_hides_and_ignores_featured_quick_filter(): void
    {
        $category = Category::factory()->create(['name' => 'Hermetikai']);
        $brand = Brand::factory()->create();

        $featuredProduct = $this->createPublishedProduct([
            'name'        => 'Teminis hermetikas',
            'brand_id'    => $brand->id,
            'is_featured' => true,
        ]);
        $featuredProduct->categories()->attach($category->id);

        $regularProduct = $this->createPublishedProduct([
            'name'        => 'Standartinis hermetikas',
            'brand_id'    => $brand->id,
            'is_featured' => false,
        ]);
        $regularProduct->categories()->attach($category->id);

        $response = $this->get(route('frontend.categories.show', [
            'category' => $category,
            'filter'   => 'featured',
        ]));

        $response->assertOk()
            ->assertDontSeeText('Tik rekomenduojamos')
            ->assertViewHas('availableFilters', fn (array $filters): bool => ! array_key_exists('featured', $filters))
            ->assertViewHas('activeFilter', fn ($filter): bool => $filter === null)
            ->assertViewHas('products', function ($paginator) use ($featuredProduct, $regularProduct): bool {
                return $paginator->contains('id', $featuredProduct->id)
                    && $paginator->contains('id', $regularProduct->id);
            });
    }

    private function createPublishedProduct(array $overrides = []): Product
    {
        return Product::factory()->create(array_merge([
            'status'       => 'published',
            'is_visible'   => true,
            'is_enabled'   => true,
            'published_at' => Carbon::now()->subDay(),
        ], $overrides));
    }
}
