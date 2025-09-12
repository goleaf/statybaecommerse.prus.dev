<?php declare(strict_types=1);

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User;
use Livewire\Livewire;

it('can perform live search', function () {
    $product = Product::factory()->create(['name' => 'Test Product', 'is_visible' => true]);
    $category = Category::factory()->create(['name' => 'Test Category', 'is_visible' => true]);
    $brand = Brand::factory()->create(['name' => 'Test Brand', 'is_enabled' => true]);
    
    Livewire::test(\App\Livewire\Components\LiveSearch::class)
        ->set('query', 'Test')
        ->assertSet('showResults', true)
        ->assertSee('Test Product');
});

it('can filter products with advanced search', function () {
    $brand = Brand::factory()->create(['name' => 'Test Brand']);
    $category = Category::factory()->create(['name' => 'Test Category']);
    
    $product1 = Product::factory()->create([
        'name' => 'Expensive Product',
        'brand_id' => $brand->id,
        'price' => 200.00,
        'is_visible' => true,
    ]);
    $product1->categories()->attach($category->id);
    
    $product2 = Product::factory()->create([
        'name' => 'Cheap Product',
        'price' => 50.00,
        'is_visible' => true,
    ]);
    
    Livewire::test(\App\Livewire\Components\AdvancedProductSearch::class)
        ->set('minPrice', 100.00)
        ->set('maxPrice', 300.00)
        ->assertSee('Expensive Product')
        ->assertDontSee('Cheap Product');
});

it('can manage product comparison', function () {
    $product1 = Product::factory()->create(['name' => 'Product 1', 'price' => 100.00]);
    $product2 = Product::factory()->create(['name' => 'Product 2', 'price' => 150.00]);
    
    Livewire::test(\App\Livewire\Components\ProductComparison::class)
        ->call('addProduct', $product1->id)
        ->call('addProduct', $product2->id)
        ->assertSee('Product 1')
        ->assertSee('Product 2')
        ->assertSee('€100.00')
        ->assertSee('€150.00');
});

it('can use product quick view', function () {
    $product = Product::factory()->create([
        'name' => 'Quick View Product',
        'price' => 99.99,
        'is_visible' => true,
    ]);
    
    Livewire::test(\App\Livewire\Components\ProductQuickView::class)
        ->dispatch('product-quick-view', productId: $product->id)
        ->assertSet('showModal', true)
        ->assertSee('Quick View Product')
        ->assertSee('€99.99');
});

it('can manage wishlist with authentication', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    
    Livewire::actingAs($user)
        ->test(\App\Livewire\Components\AdvancedWishlist::class)
        ->call('toggleWishlist', $product->id)
        ->assertDispatched('wishlist-added');
        
    expect($user->fresh()->wishlist)->toHaveCount(1);
});

it('can manage wishlist without authentication', function () {
    $product = Product::factory()->create();
    
    Livewire::test(\App\Livewire\Components\AdvancedWishlist::class)
        ->call('toggleWishlist', $product->id)
        ->assertDispatched('wishlist-added');
        
    expect(session('wishlist'))->toContain($product->id);
});

it('can clear entire wishlist', function () {
    $user = User::factory()->create();
    $products = Product::factory()->count(3)->create();
    
    $user->wishlist()->attach($products->pluck('id'));
    
    Livewire::actingAs($user)
        ->test(\App\Livewire\Components\AdvancedWishlist::class)
        ->call('clearWishlist')
        ->assertDispatched('wishlist-cleared');
        
    expect($user->fresh()->wishlist)->toHaveCount(0);
});

it('can handle product catalog with filters', function () {
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();
    
    $product = Product::factory()->create([
        'brand_id' => $brand->id,
        'is_visible' => true,
        'published_at' => now(),
    ]);
    $product->categories()->attach($category->id);
    
    Livewire::test(\App\Livewire\Pages\ProductCatalog::class)
        ->set('brandId', $brand->id)
        ->assertSee($product->name);
});



