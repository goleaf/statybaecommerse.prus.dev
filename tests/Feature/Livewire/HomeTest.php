<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\Home;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_home(): void
    {
        Livewire::test(Home::class)->assertOk();
    }

    public function test_displays_featured_products(): void
    {
        $featuredProduct = Product::factory()->create([
            'is_visible' => true,
            'is_featured' => true,
            'published_at' => now()->subDay(),
        ]);

        Livewire::test(Home::class)
            ->assertViewHas('featuredProducts', function ($products) use ($featuredProduct) {
                return $products->pluck('id')->contains($featuredProduct->id);
            });
    }

    public function test_displays_latest_products(): void
    {
        $newerProduct = Product::factory()->create([
            'is_visible' => true,
            'published_at' => now()->subDay(),
            'created_at' => now()->subHour(),
        ]);

        Livewire::test(Home::class)
            ->assertViewHas('latestProducts', function ($products) use ($newerProduct) {
                return $products->pluck('id')->contains($newerProduct->id);
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

        Livewire::test(Home::class)
            ->assertViewHas('latestReviews', function ($reviews) use ($approvedReview) {
                return $reviews->pluck('id')->contains($approvedReview->id);
            });
    }

    public function test_can_add_product_to_cart_from_homepage(): void
    {
        $product = Product::factory()->create([
            'is_visible' => true,
            'stock_quantity' => 10,
            'published_at' => now()->subDay(),
        ]);

        Livewire::test(Home::class)
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

        Livewire::test(Home::class)
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

        Livewire::test(Home::class)
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

        Livewire::test(Home::class)
            ->assertViewHas('featuredProducts', function ($products) use ($publishedProduct, $unpublishedProduct) {
                $ids = $products->pluck('id');

                return $ids->contains($publishedProduct->id) && ! $ids->contains($unpublishedProduct->id);
            });
    }
}
