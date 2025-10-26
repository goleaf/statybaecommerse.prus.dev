<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_products_and_allows_search(): void
    {
        $matching = $this->createVisibleProduct(['name' => 'Lithuanian Hammer']);
        $this->createVisibleProduct(['name' => 'Non matching item']);

        $response = $this->get(route('frontend.products.index', ['search' => 'hammer']));

        $response->assertOk();
        $response->assertViewIs('frontend.products.index');
        $response->assertViewHas('products', fn ($products) => $products->contains($matching));
    }

    public function test_search_route_delegates_to_index(): void
    {
        $product = $this->createVisibleProduct(['name' => 'Cordless Drill']);

        $response = $this->get(route('frontend.products.search', ['q' => 'Cordless']));

        $response->assertOk();
        $response->assertViewHas('products', fn ($products) => $products->contains($product));
    }

    public function test_by_category_returns_products_within_category(): void
    {
        $category = Category::factory()->create(['is_visible' => true]);
        $product = $this->createVisibleProduct();
        $product->categories()->attach($category->id);

        $response = $this->get(route('frontend.products.by-category', $category));

        $response->assertOk();
        $response->assertViewHas('currentCategory', $category);
        $response->assertViewHas('products', fn ($products) => $products->contains($product));
    }

    public function test_by_brand_returns_products_from_brand(): void
    {
        $brand = Brand::factory()->create([
            'is_active'  => true,
            'is_enabled' => true,
            'is_visible' => true,
        ]);
        $product = $this->createVisibleProduct(['brand_id' => $brand->id]);

        $response = $this->get(route('frontend.products.by-brand', $brand));

        $response->assertOk();
        $response->assertViewHas('currentBrand', $brand);
        $response->assertViewHas('products', fn ($products) => $products->contains($product));
    }

    public function test_show_displays_product_details(): void
    {
        $product = $this->createVisibleProduct(['name' => 'Premium Drill']);

        $response = $this->get(route('frontend.products.show', $product));

        $response->assertOk();
        $response->assertViewIs('frontend.products.show');
        $response->assertViewHas('product', fn ($viewProduct) => $viewProduct->is($product));
    }

    public function test_add_review_requires_rating(): void
    {
        $product = $this->createVisibleProduct();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('frontend.products.add-review', $product), [
            'content' => 'Great product!',
        ]);

        $response->assertSessionHasErrors('rating');
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_add_review_creates_review(): void
    {
        $product = $this->createVisibleProduct();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('frontend.products.add-review', $product), [
            'rating'  => 5,
            'title'   => 'Amazing',
            'content' => 'Loved using this tool.',
        ]);

        $response->assertRedirect(route('frontend.products.show', $product));
        $this->assertDatabaseHas(Review::class, [
            'product_id' => $product->id,
            'user_id'    => $user->id,
            'rating'     => 5,
            'title'      => 'Amazing',
        ]);
    }

    private function createVisibleProduct(array $overrides = []): Product
    {
        $brand = Brand::factory()->create([
            'is_active'  => true,
            'is_enabled' => true,
            'is_visible' => true,
        ]);
        $category = Category::factory()->create(['is_visible' => true]);

        $product = Product::factory()->create(array_merge([
            'brand_id'     => $overrides['brand_id'] ?? $brand->id,
            'is_visible'   => true,
            'status'       => 'active',
            'published_at' => now()->subHour(),
        ], $overrides));

        $product->categories()->sync([$category->id]);

        return $product;
    }
}
