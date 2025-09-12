<?php declare(strict_types=1);

use App\Livewire\Pages\Home;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use Livewire\Livewire;

it('renders home page', function () {
    $this->get('/')
        ->assertRedirect('/lt'); // Root redirects to localized home
    
    // Test the actual localized home page
    $this->get('/lt')
        ->assertOk()
        ->assertSee('Profesionalūs įrankiai'); // Check for Lithuanian text
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
    
    Livewire::test(Home::class)
        ->assertSee('Featured Product')
        ->assertSee('Test Brand');
});

// Removed legacy collections/search tests; not applicable to Enhanced Home

it('can add product to cart', function () {
    $product = Product::factory()->create([
        'is_visible' => true,
        'published_at' => now(),
    ]);
    
    Livewire::test(Home::class)
        ->call('addToCart', $product->id)
        ->assertDispatched('cart-updated');
});
