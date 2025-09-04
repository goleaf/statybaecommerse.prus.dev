<?php declare(strict_types=1);

namespace Tests\Feature\System;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ComprehensiveSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_has_all_required_models(): void
    {
        $this->assertTrue(class_exists(User::class));
        $this->assertTrue(class_exists(Product::class));
        $this->assertTrue(class_exists(Category::class));
        $this->assertTrue(class_exists(Brand::class));
        $this->assertTrue(class_exists(Order::class));
        $this->assertTrue(class_exists(Setting::class));
    }

    public function test_database_tables_exist(): void
    {
        $this->assertDatabaseTableExists('users');
        $this->assertDatabaseTableExists('products');
        $this->assertDatabaseTableExists('categories');
        $this->assertDatabaseTableExists('brands');
        $this->assertDatabaseTableExists('orders');
        $this->assertDatabaseTableExists('settings');
    }

    public function test_admin_panel_routes_are_accessible(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $this->get('/admin')
            ->assertOk();

        $this->get('/admin/login')
            ->assertRedirect('/admin'); // Should redirect if already logged in
    }

    public function test_frontend_routes_are_accessible(): void
    {
        $this->get('/')
            ->assertRedirect('/home');

        $this->get('/home')
            ->assertOk();

        $this->get('/products')
            ->assertOk();

        $this->get('/categories')
            ->assertOk();

        $this->get('/brands')
            ->assertOk();
    }

    public function test_translation_system_works(): void
    {
        app()->setLocale('en');
        $this->assertEquals('Products', __('ecommerce.products'));

        app()->setLocale('lt');
        $this->assertEquals('Produktai', __('ecommerce.products'));
    }

    public function test_models_have_proper_relationships(): void
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id]);
        $product->categories()->attach($category);

        $this->assertInstanceOf(Brand::class, $product->brand);
        $this->assertTrue($product->categories->contains($category));
        $this->assertTrue($brand->products->contains($product));
        $this->assertTrue($category->products->contains($product));
    }

    public function test_permissions_system_works(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin);
        $this->get('/admin')->assertOk();

        $this->actingAs($customer);
        $this->get('/admin')->assertRedirect(); // Should redirect non-admin users
    }

    public function test_settings_system_works(): void
    {
        Setting::factory()->create([
            'key' => 'test_setting',
            'value' => 'test_value',
        ]);

        $this->assertEquals('test_value', shopper_setting('test_setting'));
    }

    public function test_cart_session_functionality(): void
    {
        $product = Product::factory()->create([
            'is_visible' => true,
            'stock_quantity' => 10,
        ]);

        session()->put('cart', [
            $product->id => [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 2,
            ]
        ]);

        $cart = session('cart');
        $this->assertArrayHasKey($product->id, $cart);
        $this->assertEquals(2, $cart[$product->id]['quantity']);
    }

    public function test_multilingual_content_works(): void
    {
        $category = Category::factory()->create();
        
        // Create translations
        $category->translations()->create([
            'locale' => 'en',
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);
        
        $category->translations()->create([
            'locale' => 'lt',
            'name' => 'Elektronika',
            'slug' => 'elektronika',
        ]);

        app()->setLocale('en');
        $this->assertEquals('Electronics', $category->translate('name'));

        app()->setLocale('lt');
        $this->assertEquals('Elektronika', $category->translate('name'));
    }

    public function test_product_visibility_and_publishing_works(): void
    {
        $visibleProduct = Product::factory()->create([
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);
        
        $hiddenProduct = Product::factory()->create([
            'is_visible' => false,
        ]);
        
        $unpublishedProduct = Product::factory()->create([
            'is_visible' => true,
            'published_at' => now()->addDay(),
        ]);

        $visibleProducts = Product::where('is_visible', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                      ->orWhere('published_at', '<=', now());
            })
            ->get();

        $this->assertTrue($visibleProducts->contains($visibleProduct));
        $this->assertFalse($visibleProducts->contains($hiddenProduct));
        $this->assertFalse($visibleProducts->contains($unpublishedProduct));
    }

    private function assertDatabaseTableExists(string $table): void
    {
        $this->assertTrue(
            \Schema::hasTable($table),
            "Database table '{$table}' does not exist"
        );
    }
}
