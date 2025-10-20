<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class ProductBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_lists_only_published_products(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $visibleProduct = $this->createPublishedProduct([
            'name' => 'Profesionalus pjūklas',
            'slug' => 'profesionalus-pjuklas',
            'brand_id' => $brand->id,
        ]);
        $visibleProduct->categories()->attach($category->id);

        $hiddenProduct = $this->createPublishedProduct([
            'name' => 'Senas produktas',
            'slug' => 'senas-produktas',
            'status' => 'draft',
            'is_visible' => false,
        ]);
        $hiddenProduct->categories()->attach($category->id);

        $response = $this->get(route('frontend.products.index'));

        $response->assertOk()
            ->assertViewIs('products.index')
            ->assertViewHas('products', function ($paginator) use ($visibleProduct, $hiddenProduct) {
                return $paginator->contains('id', $visibleProduct->id)
                    && ! $paginator->contains('id', $hiddenProduct->id);
            });
    }

    public function test_search_filters_products_by_query(): void
    {
        $brand = Brand::factory()->create();
        $matchingProduct = $this->createPublishedProduct(['name' => 'Mighty Hammer', 'slug' => 'mighty-hammer', 'brand_id' => $brand->id]);
        $otherProduct = $this->createPublishedProduct(['name' => 'Safety Helmet', 'slug' => 'safety-helmet']);

        $response = $this->get(route('frontend.products.search', ['q' => 'Hammer']));

        $response->assertOk()
            ->assertViewIs('products.index')
            ->assertViewHas('products', function ($paginator) use ($matchingProduct, $otherProduct) {
                return $paginator->contains('id', $matchingProduct->id)
                    && ! $paginator->contains('id', $otherProduct->id);
            });
    }

    public function test_product_show_returns_404_for_unpublished_product(): void
    {
        $product = $this->createPublishedProduct([
            'status' => 'draft',
            'is_visible' => false,
            'published_at' => null,
        ]);

        $this->get(route('frontend.products.show', $product))->assertNotFound();
    }

    public function test_product_show_renders_for_published_product(): void
    {
        $product = $this->createPublishedProduct(['name' => 'Universal Drill', 'slug' => 'universal-drill']);

        $this->get(route('frontend.products.show', $product))
            ->assertOk()
            ->assertViewIs('products.show')
            ->assertSee('Universal Drill');
    }

    public function test_add_review_creates_pending_review(): void
    {
        $product = $this->createPublishedProduct();

        $payload = [
            'reviewer_name' => 'Jonas',
            'reviewer_email' => 'jonas@example.com',
            'rating' => 5,
            'title' => 'Puikus įrankis',
            'content' => 'Įrankis pateisino visus lūkesčius.',
        ];

        $response = $this->post(route('frontend.products.add-review', $product), $payload);

        $response->assertRedirect(route('frontend.products.show', $product));

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'reviewer_email' => 'jonas@example.com',
            'content' => 'Įrankis pateisino visus lūkesčius.',
            'is_approved' => false,
        ]);
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
