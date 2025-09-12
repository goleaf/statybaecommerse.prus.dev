<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
    }

    public function test_admin_can_view_inventory_management_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/inventory-management');

        $response->assertOk();
        $response->assertSee('Inventory Management');
    }

    public function test_inventory_management_page_shows_products(): void
    {
        $brand = Brand::factory()->create(['name' => 'Test Brand']);
        $category = Category::factory()->create(['name' => 'Test Category']);
        
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'brand_id' => $brand->id,
            'manage_stock' => true,
            'stock_quantity' => 10,
            'low_stock_threshold' => 5,
        ]);

        $product->categories()->attach($category);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/inventory-management');

        $response->assertOk();
        $response->assertSee('Test Product');
        $response->assertSee('TEST-001');
        $response->assertSee('Test Brand');
    }

    public function test_inventory_management_page_shows_stock_status(): void
    {
        Product::factory()->create([
            'name' => 'In Stock Product',
            'manage_stock' => true,
            'stock_quantity' => 10,
            'low_stock_threshold' => 5,
        ]);

        Product::factory()->create([
            'name' => 'Low Stock Product',
            'manage_stock' => true,
            'stock_quantity' => 3,
            'low_stock_threshold' => 5,
        ]);

        Product::factory()->create([
            'name' => 'Out of Stock Product',
            'manage_stock' => true,
            'stock_quantity' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/inventory-management');

        $response->assertOk();
        $response->assertSee('In Stock Product');
        $response->assertSee('Low Stock Product');
        $response->assertSee('Out of Stock Product');
    }

    public function test_inventory_management_page_shows_summary_cards(): void
    {
        Product::factory()->count(10)->create([
            'manage_stock' => true,
            'stock_quantity' => 10,
            'low_stock_threshold' => 5,
        ]);

        Product::factory()->count(3)->create([
            'manage_stock' => true,
            'stock_quantity' => 3,
            'low_stock_threshold' => 5,
        ]);

        Product::factory()->count(2)->create([
            'manage_stock' => true,
            'stock_quantity' => 0,
        ]);

        Product::factory()->count(5)->create([
            'manage_stock' => false,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/inventory-management');

        $response->assertOk();
        $response->assertSee('20'); // Total products
        $response->assertSee('15'); // Tracked products
        $response->assertSee('10'); // In stock
        $response->assertSee('3'); // Low stock
        $response->assertSee('2'); // Out of stock
        $response->assertSee('5'); // Not tracked
    }

    public function test_inventory_management_page_filters_work(): void
    {
        Product::factory()->create([
            'name' => 'In Stock Product',
            'manage_stock' => true,
            'stock_quantity' => 10,
            'low_stock_threshold' => 5,
        ]);

        Product::factory()->create([
            'name' => 'Low Stock Product',
            'manage_stock' => true,
            'stock_quantity' => 3,
            'low_stock_threshold' => 5,
        ]);

        Product::factory()->create([
            'name' => 'Out of Stock Product',
            'manage_stock' => true,
            'stock_quantity' => 0,
        ]);

        // Test low stock filter
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/inventory-management?tableFilters[stock_status][value]=low_stock');

        $response->assertOk();
        $response->assertSee('Low Stock Product');
        $response->assertDontSee('In Stock Product');
        $response->assertDontSee('Out of Stock Product');

        // Test out of stock filter
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/inventory-management?tableFilters[stock_status][value]=out_of_stock');

        $response->assertOk();
        $response->assertSee('Out of Stock Product');
        $response->assertDontSee('In Stock Product');
        $response->assertDontSee('Low Stock Product');
    }

    public function test_inventory_management_page_search_works(): void
    {
        Product::factory()->create([
            'name' => 'Special Product',
            'sku' => 'SPEC-001',
        ]);

        Product::factory()->create([
            'name' => 'Regular Product',
            'sku' => 'REG-001',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/inventory-management?tableSearch=Special');

        $response->assertOk();
        $response->assertSee('Special Product');
        $response->assertDontSee('Regular Product');
    }

    public function test_inventory_management_page_brand_filter_works(): void
    {
        $brand1 = Brand::factory()->create(['name' => 'Brand A']);
        $brand2 = Brand::factory()->create(['name' => 'Brand B']);

        Product::factory()->create([
            'name' => 'Product A',
            'brand_id' => $brand1->id,
        ]);

        Product::factory()->create([
            'name' => 'Product B',
            'brand_id' => $brand2->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/inventory-management?tableFilters[brand][value]=' . $brand1->id);

        $response->assertOk();
        $response->assertSee('Product A');
        $response->assertDontSee('Product B');
    }

    public function test_inventory_management_page_tracking_filter_works(): void
    {
        Product::factory()->create([
            'name' => 'Tracked Product',
            'manage_stock' => true,
        ]);

        Product::factory()->create([
            'name' => 'Not Tracked Product',
            'manage_stock' => false,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/inventory-management?tableFilters[manage_stock][value]=1');

        $response->assertOk();
        $response->assertSee('Tracked Product');
        $response->assertDontSee('Not Tracked Product');
    }
}
