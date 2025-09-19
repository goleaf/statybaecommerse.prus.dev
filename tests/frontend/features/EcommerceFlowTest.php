<?php declare(strict_types=1);

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use App\Models\Order;
use App\Models\CartItem;

it('can view product catalog', function () {
    $response = $this->get('/');
    
    $response->assertStatus(302); // Redirects to locale
});

it('can view localized product catalog', function () {
    Product::factory()->count(5)->create(['is_visible' => true]);
    
    $response = $this->get('/lt');
    
    $response->assertStatus(200);
});

it('can view brand pages', function () {
    $brand = Brand::factory()->create(['is_enabled' => true]);
    
    $response = $this->get('/lt/brands');
    
    $response->assertStatus(200);
});

it('can view category pages', function () {
    $category = Category::factory()->create(['is_enabled' => true]);
    
    $response = $this->get('/lt/categories');
    
    $response->assertStatus(200);
});

it('can view single product', function () {
    $product = Product::factory()->create([
        'is_visible' => true,
        'slug' => 'test-product',
    ]);
    
    $response = $this->get('/lt/products/test-product');
    
    $response->assertStatus(200);
});

it('can access cart page', function () {
    $response = $this->get('/lt/cart');
    
    $response->assertStatus(200);
});

it('can access search page', function () {
    $response = $this->get('/lt/search');
    
    $response->assertStatus(200);
});

it('user can access account pages when authenticated', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/account');
    
    $response->assertStatus(200);
});

it('redirects unauthenticated users from account', function () {
    $response = $this->get('/account');
    
    $response->assertRedirect();
});
