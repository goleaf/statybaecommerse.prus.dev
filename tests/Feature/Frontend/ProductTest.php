<?php declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Livewire\Pages\SingleProduct;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_published_product(): void
    {
        $product = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('product.show', [
            'locale' => 'en',
            'slug' => $product->slug,
        ]));

        $response->assertOk();
        $response->assertSee($product->name);
        $response->assertSee($product->description);
    }

    public function test_cannot_view_unpublished_product(): void
    {
        $product = Product::factory()->create([
            'is_visible' => false,
            'status' => 'draft',
        ]);

        $response = $this->get(route('product.show', [
            'locale' => 'en',
            'slug' => $product->slug,
        ]));

        $response->assertNotFound();
    }

    public function test_can_add_product_to_cart(): void
    {
        $product = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'stock_quantity' => 10,
        ]);

        Livewire::test(SingleProduct::class, ['slug' => $product->slug])
            ->set('quantity', 2)
            ->call('addToCart')
            ->assertDispatched('cart-updated');

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_cannot_add_out_of_stock_product_to_cart(): void
    {
        $product = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'stock_quantity' => 0,
            'manage_stock' => true,
        ]);

        Livewire::test(SingleProduct::class, ['slug' => $product->slug])
            ->set('quantity', 1)
            ->call('addToCart')
            ->assertHasErrors(['quantity']);
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        $product = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'stock_quantity' => 5,
            'manage_stock' => true,
        ]);

        Livewire::test(SingleProduct::class, ['slug' => $product->slug])
            ->set('quantity', 10)
            ->call('addToCart')
            ->assertHasErrors(['quantity']);
    }

    public function test_product_displays_correct_pricing(): void
    {
        $product = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'price' => 99.99,
            'compare_price' => 129.99,
        ]);

        $component = Livewire::test(SingleProduct::class, ['slug' => $product->slug]);

        $component->assertSee('€99.99');
        $component->assertSee('€129.99');
    }

    public function test_product_shows_brand_information(): void
    {
        $brand = Brand::factory()->create(['name' => 'Test Brand']);
        $product = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'brand_id' => $brand->id,
        ]);

        Livewire::test(SingleProduct::class, ['slug' => $product->slug])
            ->assertSee('Test Brand');
    }

    public function test_product_shows_category_information(): void
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        $product = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        
        $product->categories()->attach($category);

        Livewire::test(SingleProduct::class, ['slug' => $product->slug])
            ->assertSee('Test Category');
    }

    public function test_product_displays_reviews(): void
    {
        $product = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $reviews = Review::factory()->count(3)->create([
            'product_id' => $product->id,
            'is_approved' => true,
            'rating' => 5,
        ]);

        $component = Livewire::test(SingleProduct::class, ['slug' => $product->slug]);

        foreach ($reviews as $review) {
            $component->assertSee($review->content);
            $component->assertSee($review->reviewer_name);
        }
    }

    public function test_product_shows_correct_average_rating(): void
    {
        $product = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        // Create reviews with ratings: 5, 4, 3 (average = 4.0)
        Review::factory()->create([
            'product_id' => $product->id,
            'is_approved' => true,
            'rating' => 5,
        ]);
        Review::factory()->create([
            'product_id' => $product->id,
            'is_approved' => true,
            'rating' => 4,
        ]);
        Review::factory()->create([
            'product_id' => $product->id,
            'is_approved' => true,
            'rating' => 3,
        ]);

        $component = Livewire::test(SingleProduct::class, ['slug' => $product->slug]);
        
        // Should show average rating of 4.0
        $component->assertSee('4.0');
    }

    public function test_product_quantity_validation(): void
    {
        $product = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'stock_quantity' => 10,
        ]);

        // Test negative quantity
        Livewire::test(SingleProduct::class, ['slug' => $product->slug])
            ->set('quantity', -1)
            ->call('addToCart')
            ->assertHasErrors(['quantity']);

        // Test zero quantity
        Livewire::test(SingleProduct::class, ['slug' => $product->slug])
            ->set('quantity', 0)
            ->call('addToCart')
            ->assertHasErrors(['quantity']);

        // Test valid quantity
        Livewire::test(SingleProduct::class, ['slug' => $product->slug])
            ->set('quantity', 2)
            ->call('addToCart')
            ->assertHasNoErrors();
    }

    public function test_product_seo_meta_tags(): void
    {
        $product = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'name' => 'Test Product',
            'seo_title' => 'Custom SEO Title',
            'seo_description' => 'Custom SEO Description',
        ]);

        $response = $this->get(route('product.show', [
            'locale' => 'en',
            'slug' => $product->slug,
        ]));

        $response->assertSee('<title>Custom SEO Title</title>', false);
        $response->assertSee('<meta name="description" content="Custom SEO Description">', false);
    }
}
