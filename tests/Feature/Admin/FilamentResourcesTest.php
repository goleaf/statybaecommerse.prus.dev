<?php declare(strict_types=1);

use App\Models\User;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\EnhancedSetting;

it('can access admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    
    $this->actingAs($user)
        ->get('/admin')
        ->assertOk();
});

it('can access product resource', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    
    $this->actingAs($user)
        ->get('/admin/products')
        ->assertOk();
});

it('can create product through admin', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();
    
    $this->actingAs($user)
        ->post('/admin/products', [
            'name' => 'Test Product',
            'slug' => 'test-product',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'price' => 99.99,
            'is_visible' => true,
        ])
        ->assertRedirect();
        
    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'slug' => 'test-product',
    ]);
});

it('can access enhanced settings resource', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    
    $this->actingAs($user)
        ->get('/admin/enhanced-settings')
        ->assertOk();
});

it('can create enhanced setting', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    
    $this->actingAs($user);
    
    $setting = EnhancedSetting::create([
        'key' => 'test_setting',
        'value' => 'test_value',
        'type' => 'text',
        'group' => 'test',
    ]);
    
    expect($setting->getValue())->toBe('test_value');
    expect(EnhancedSetting::get('test_setting'))->toBe('test_value');
});
