<?php declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\EnhancedHome;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class EnhancedHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_enhanced_home(): void
    {
        Livewire::test(EnhancedHome::class)
            ->assertOk();
    }

    public function test_displays_featured_products(): void
    {
        $featuredProduct = Product::factory()->create([
            'is_visible' => true,
            'is_featured' => true,
            'published_at' => now()->subDay(),
        ]);
        
        $regularProduct = Product::factory()->create([
            'is_visible' => true,
            'is_featured' => false,
            'published_at' => now()->subDay(),
        ]);

        Livewire::test(EnhancedHome::class)
            ->assertViewHas('featuredProducts', function ($products) use ($featuredProduct, $regularProduct) {
                $productIds = $products->pluck('id')->toArray();
                return in_array($featuredProduct->id, $productIds) &&
                       !in_array($regularProduct->id, $productIds);
            });
    }

    public function test_displays_latest_products(): void
    {
        $newerProduct = Product::factory()->create([
            'is_visible' => true,
            'published_at' => now()->subDay(),
            'created_at' => now()->subHour(),
        ]);
        
        $olderProduct = Product::factory()->create([
            'is_visible' => true,
            'published_at' => now()->subDay(),
            'created_at' => now()->subWeek(),
        ]);

        Livewire::test(EnhancedHome::class)
            ->assertViewHas('latestProducts', function ($products) use ($newerProduct, $olderProduct) {
                $productIds = $products->pluck('id')->toArray();
                $newerIndex = array_search($newerProduct->id, $productIds);
                $olderIndex = array_search($olderProduct->id, $productIds);
                
                // Newer product should appear before older product (lower index)
                return $newerIndex !== false && $olderIndex !== false && $newerIndex < $olderIndex;
            });
    }

    public function test_displays_featured_categories(): void
    {
        $featuredCategory = Category::factory()->create([
            'is_visible' => true,
            'is_featured' => true,
        ]);
        
        $regularCategory = Category::factory()->create([
            'is_visible' => true,
            'is_featured' => false,
        ]);

        // Add products to categories so they show up
        Product::factory()->create(['is_visible' => true])->categories()->attach($featuredCategory);
        Product::factory()->create(['is_visible' => true])->categories()->attach($regularCategory);

        Livewire::test(EnhancedHome::class)
            ->assertViewHas('featuredCategories', function ($categories) use ($featuredCategory, $regularCategory) {
                $categoryIds = $categories->pluck('id')->toArray();
                return in_array($featuredCategory->id, $categoryIds) &&
                       !in_array($regularCategory->id, $categoryIds);
            });
    }

    public function test_displays_featured_brands(): void
    {
        $featuredBrand = Brand::factory()->create([
            'is_visible' => true,
            'is_featured' => true,
        ]);
        
        $regularBrand = Brand::factory()->create([
            'is_visible' => true,
            'is_featured' => false,
        ]);

        // Add products to brands so they show up
        Product::factory()->create([
            'is_visible' => true,
            'brand_id' => $featuredBrand->id,
        ]);
        Product::factory()->create([
            'is_visible' => true,
            'brand_id' => $regularBrand->id,
        ]);

        Livewire::test(EnhancedHome::class)
            ->assertViewHas('featuredBrands', function ($brands) use ($featuredBrand, $regularBrand) {
                $brandIds = $brands->pluck('id')->toArray();
                return in_array($featuredBrand->id, $brandIds) &&
                       !in_array($regularBrand->id, $brandIds);
            });
    }

    public function test_displays_latest_reviews(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['is_visible' => true]);
        
        $approvedReview = Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'is_approved' => true,
        ]);
        
        $pendingReview = Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'is_approved' => false,
        ]);

        Livewire::test(EnhancedHome::class)
            ->assertViewHas('latestReviews', function ($reviews) use ($approvedReview, $pendingReview) {
                $reviewIds = $reviews->pluck('id')->toArray();
                return in_array($approvedReview->id, $reviewIds) &&
                       !in_array($pendingReview->id, $reviewIds);
            });
    }

    public function test_calculates_correct_stats(): void
    {
        // Create test data
        Product::factory()->count(10)->create(['is_visible' => true]);
        Category::factory()->count(5)->create(['is_visible' => true]);
        Brand::factory()->count(3)->create(['is_visible' => true]);
        
        $user = User::factory()->create();
        $product = Product::factory()->create(['is_visible' => true]);
        Review::factory()->count(4)->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'is_approved' => true,
            'rating' => 4,
        ]);

        Livewire::test(EnhancedHome::class)
            ->assertViewHas('stats', function ($stats) {
                return $stats['products_count'] === 11 && // 10 + 1 from review test
                       $stats['categories_count'] === 5 &&
                       $stats['brands_count'] === 3 &&
                       $stats['reviews_count'] === 4 &&
                       $stats['avg_rating'] == 4.0;
            });
    }

    public function test_can_add_product_to_cart_from_homepage(): void
    {
        $product = Product::factory()->create([
            'is_visible' => true,
            'stock_quantity' => 10,
            'published_at' => now()->subDay(),
        ]);

        Livewire::test(EnhancedHome::class)
            ->call('addToCart', $product->id)
            ->assertDispatched('cart-updated')
            ->assertDispatched('notify');

        $cart = session('cart', []);
        expect($cart)->toHaveKey($product->id);
        expect($cart[$product->id]['quantity'])->toBe(1);
    }

    public function test_cannot_add_out_of_stock_product_to_cart(): void
    {
        $product = Product::factory()->create([
            'is_visible' => true,
            'stock_quantity' => 0,
            'published_at' => now()->subDay(),
        ]);

        Livewire::test(EnhancedHome::class)
            ->call('addToCart', $product->id)
            ->assertNotDispatched('cart-updated')
            ->assertDispatched('notify');

        $cart = session('cart', []);
        expect($cart)->not->toHaveKey($product->id);
    }

    public function test_cannot_add_invisible_product_to_cart(): void
    {
        $product = Product::factory()->create([
            'is_visible' => false,
            'stock_quantity' => 10,
        ]);

        Livewire::test(EnhancedHome::class)
            ->call('addToCart', $product->id)
            ->assertNotDispatched('cart-updated')
            ->assertDispatched('notify');

        $cart = session('cart', []);
        expect($cart)->not->toHaveKey($product->id);
    }

    public function test_only_shows_published_products(): void
    {
        $publishedProduct = Product::factory()->create([
            'is_visible' => true,
            'is_featured' => true,
            'published_at' => now()->subDay(),
        ]);
        
        $unpublishedProduct = Product::factory()->create([
            'is_visible' => true,
            'is_featured' => true,
            'published_at' => now()->addDay(),
        ]);

        Livewire::test(EnhancedHome::class)
            ->assertViewHas('featuredProducts', function ($products) use ($publishedProduct, $unpublishedProduct) {
                $productIds = $products->pluck('id')->toArray();
                return in_array($publishedProduct->id, $productIds) &&
                       !in_array($unpublishedProduct->id, $productIds);
            });
    }

    public function test_respects_sort_order_for_featured_items(): void
    {
        $firstCategory = Category::factory()->create([
            'is_visible' => true,
            'is_featured' => true,
            'sort_order' => 1,
        ]);
        
        $secondCategory = Category::factory()->create([
            'is_visible' => true,
            'is_featured' => true,
            'sort_order' => 2,
        ]);

        // Add products to categories
        Product::factory()->create(['is_visible' => true])->categories()->attach($firstCategory);
        Product::factory()->create(['is_visible' => true])->categories()->attach($secondCategory);

        Livewire::test(EnhancedHome::class)
            ->assertViewHas('featuredCategories', function ($categories) use ($firstCategory, $secondCategory) {
                $categoryIds = $categories->pluck('id')->toArray();
                $firstIndex = array_search($firstCategory->id, $categoryIds);
                $secondIndex = array_search($secondCategory->id, $categoryIds);
                
                return $firstIndex !== false && $secondIndex !== false && $firstIndex < $secondIndex;
            });
    }
}
