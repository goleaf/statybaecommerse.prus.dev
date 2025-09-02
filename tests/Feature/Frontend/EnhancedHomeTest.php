<?php declare(strict_types=1);

use App\Livewire\Pages\EnhancedHome;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use Livewire\Livewire;

it('renders enhanced home page', function () {
    $this->get('/')
        ->assertOk()
        ->assertSeeLivewire(EnhancedHome::class);
});

it('displays featured products', function () {
    $brand = Brand::factory()->create(['name' => 'Test Brand']);
    $product = Product::factory()->create([
        'name' => 'Featured Product',
        'is_visible' => true,
        'is_featured' => true,
        'brand_id' => $brand->id,
        'published_at' => now(),
    ]);
    
    Livewire::test(EnhancedHome::class)
        ->assertSee('Featured Product')
        ->assertSee('Test Brand');
});

it('displays featured collections', function () {
    $collection = Collection::factory()->create([
        'name' => 'Featured Collection',
        'is_enabled' => true,
        'is_featured' => true,
    ]);
    
    Livewire::test(EnhancedHome::class)
        ->assertSee('Featured Collection');
});

it('can search from home page', function () {
    Livewire::test(EnhancedHome::class)
        ->set('searchQuery', 'test product')
        ->call('search')
        ->assertRedirect(route('search.index', ['q' => 'test product', 'locale' => app()->getLocale()]));
});

it('can add product to cart', function () {
    $product = Product::factory()->create([
        'is_visible' => true,
        'published_at' => now(),
    ]);
    
    Livewire::test(EnhancedHome::class)
        ->call('addToCart', $product->id)
        ->assertDispatched('cart:added');
});
