<?php declare(strict_types=1);

use App\Livewire\Components\ProductCard;
use App\Models\AnalyticsEvent;
use App\Models\Product;
use App\Models\ProductComparison;
use App\Models\User;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use Livewire\Livewire;

beforeEach(function () {
    $this->product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 99.99,
        'status' => 'published',
        'is_visible' => true,
    ]);
});

it('can render enhanced product card', function () {
    Livewire::test(ProductCard::class, ['product' => $this->product])
        ->assertSee('Test Product')
        ->assertSee('€99.99')
        ->assertStatus(200);
});

it('can add product to cart', function () {
    $component = Livewire::test(ProductCard::class, ['product' => $this->product]);
    
    // Debug: Let's see what events are actually dispatched
    $component->call('addToCart');
    
    // Check if the event was dispatched at all
    $component->assertDispatched('add-to-cart');
    
    // Check the notify event
    $component->assertDispatched('notify');
});

it('tracks analytics when adding to cart', function () {
    Livewire::test(ProductCard::class, ['product' => $this->product])
        ->call('addToCart');

    $this->assertDatabaseHas(AnalyticsEvent::class, [
        'event_type' => 'add_to_cart',
        'properties->product_id' => $this->product->id,
    ]);
});

it('can add product to wishlist when authenticated', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(ProductCard::class, ['product' => $this->product])
        ->call('toggleWishlist')
        ->assertDispatched('notify')
        ->assertDispatched('wishlist-updated');

    $this->assertDatabaseHas(UserWishlist::class, [
        'user_id' => $user->id,
        'is_default' => true,
    ]);

    $this->assertDatabaseHas(WishlistItem::class, [
        'product_id' => $this->product->id,
    ]);
});

it('shows login required message when adding to wishlist as guest', function () {
    // Ensure we're not authenticated
    auth()->logout();
    $this->assertFalse(auth()->check());
    
    Livewire::test(ProductCard::class, ['product' => $this->product])
        ->call('toggleWishlist')
        ->assertDispatched('notify');
});

it('can remove product from wishlist', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $wishlist = UserWishlist::factory()->create([
        'user_id' => $user->id,
        'is_default' => true,
    ]);

    WishlistItem::factory()->create([
        'wishlist_id' => $wishlist->id,
        'product_id' => $this->product->id,
    ]);

    Livewire::test(ProductCard::class, ['product' => $this->product])
        ->call('toggleWishlist')
        ->assertDispatched('notify');

    $this->assertDatabaseMissing(WishlistItem::class, [
        'wishlist_id' => $wishlist->id,
        'product_id' => $this->product->id,
    ]);
});

it('can add product to comparison', function () {
    Livewire::test(ProductCard::class, ['product' => $this->product])
        ->call('toggleComparison')
        ->assertDispatched('notify')
        ->assertDispatched('comparison-updated');

    $this->assertDatabaseHas(ProductComparison::class, [
        'product_id' => $this->product->id,
        'session_id' => session()->getId(),
    ]);
});

it('can remove product from comparison', function () {
    ProductComparison::factory()->create([
        'product_id' => $this->product->id,
        'session_id' => session()->getId(),
    ]);

    Livewire::test(ProductCard::class, ['product' => $this->product])
        ->call('toggleComparison')
        ->assertDispatched('notify');

    $this->assertDatabaseMissing(ProductComparison::class, [
        'product_id' => $this->product->id,
        'session_id' => session()->getId(),
    ]);
});

it('can open quick view', function () {
    Livewire::test(ProductCard::class, ['product' => $this->product])
        ->call('quickView')
        ->assertDispatched('open-quick-view');
});

it('tracks analytics when opening quick view', function () {
    Livewire::test(ProductCard::class, ['product' => $this->product])
        ->call('quickView');

    $this->assertDatabaseHas(AnalyticsEvent::class, [
        'event_type' => 'product_view',
        'properties->product_id' => $this->product->id,
        'properties->view_type' => 'quick_view',
    ]);
});

it('can navigate to product page', function () {
    Livewire::test(ProductCard::class, ['product' => $this->product])
        ->call('viewProduct')
        ->assertRedirect(route('product.show', $this->product));
});

it('tracks analytics when viewing product page', function () {
    Livewire::test(ProductCard::class, ['product' => $this->product])
        ->call('viewProduct');

    $this->assertDatabaseHas(AnalyticsEvent::class, [
        'event_type' => 'product_view',
        'properties->product_id' => $this->product->id,
        'properties->view_type' => 'full_page',
    ]);
});

it('shows correct wishlist status', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $wishlist = UserWishlist::factory()->create([
        'user_id' => $user->id,
        'is_default' => true,
    ]);

    WishlistItem::factory()->create([
        'wishlist_id' => $wishlist->id,
        'product_id' => $this->product->id,
    ]);

    Livewire::test(ProductCard::class, ['product' => $this->product])
        ->assertSet('isInWishlist', true);
});

it('shows correct comparison status', function () {
    ProductComparison::factory()->create([
        'product_id' => $this->product->id,
        'session_id' => session()->getId(),
    ]);

    Livewire::test(ProductCard::class, ['product' => $this->product])
        ->assertSet('isInComparison', true);
});

it('displays product badges correctly', function () {
    $featuredProduct = Product::factory()->create([
        'name' => 'Featured Product',
        'is_featured' => true,
        'price' => 80.00,
        'compare_price' => 100.00,
    ]);

    Livewire::test(ProductCard::class, ['product' => $featuredProduct])
        ->assertSee('Featured')
        ->assertSee('-20%'); // Discount badge
});

it('shows stock status correctly', function () {
    $outOfStockProduct = Product::factory()->create([
        'stock_quantity' => 0,
        'track_inventory' => true,
    ]);

    Livewire::test(ProductCard::class, ['product' => $outOfStockProduct])
        ->assertSee(__('translations.out_of_stock'));

    $lowStockProduct = Product::factory()->create([
        'stock_quantity' => 2,
        'low_stock_threshold' => 5,
        'track_inventory' => true,
    ]);

    Livewire::test(ProductCard::class, ['product' => $lowStockProduct])
        ->assertSee(__('translations.in_stock'))
        ->assertSee('2 ' . __('translations.left'));
});

it('refreshes status on events', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test(ProductCard::class, ['product' => $this->product])
        ->assertSet('isInWishlist', false);

    // Simulate adding to wishlist externally
    $wishlist = UserWishlist::factory()->create([
        'user_id' => $user->id,
        'is_default' => true,
    ]);

    WishlistItem::factory()->create([
        'wishlist_id' => $wishlist->id,
        'product_id' => $this->product->id,
    ]);

    $component->dispatch('wishlist-updated')
        ->assertSet('isInWishlist', true);
});
