<?php declare(strict_types=1);

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Create permissions
    $permissions = [
        'view_admin_panel', 'view_any_product', 'create_product', 'edit_product', 'delete_product',
        'view_any_order', 'create_order', 'edit_order', 'view_any_user',
    ];
    
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }
    
    // Create admin role with all permissions
    $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions($permissions);
    
    // Create admin user
    $this->admin = User::factory()->create([
        'name' => 'Test Admin',
        'email' => 'admin@test.com',
        'is_active' => true,
    ]);
    $this->admin->assignRole($adminRole);
});

it('can access advanced analytics widget', function () {
    $response = $this->actingAs($this->admin)->get('/admin');
    
    $response->assertOk();
    // Widget should be loaded on dashboard
});

it('can access advanced reports page', function () {
    $response = $this->actingAs($this->admin)->get('/admin/advanced-reports');
    
    $response->assertOk();
});

it('can generate sales report data', function () {
    // Create test data
    $product = Product::factory()->create(['price' => 100.00]);
    $order = Order::factory()->create([
        'status' => 'completed',
        'total' => 100.00,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 100.00,
        'total' => 100.00,
    ]);
    
    $reportsPage = new \App\Filament\Pages\AdvancedReports();
    $reportsPage->startDate = now()->subDays(7)->format('Y-m-d');
    $reportsPage->endDate = now()->format('Y-m-d');
    
    $salesData = $reportsPage->getSalesData();
    
    expect($salesData['totalRevenue'])->toBeGreaterThan(0);
    expect($salesData['totalOrders'])->toBeGreaterThan(0);
    expect($salesData['avgOrderValue'])->toBeGreaterThan(0);
});

it('can manage wishlist functionality', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    
    // Add to wishlist
    $user->wishlist()->attach($product->id);
    
    expect($user->wishlist)->toHaveCount(1);
    expect($user->wishlist->first()->id)->toBe($product->id);
    
    // Remove from wishlist
    $user->wishlist()->detach($product->id);
    
    expect($user->wishlist)->toHaveCount(0);
});

it('can create order items properly', function () {
    $order = Order::factory()->create();
    $product = Product::factory()->create(['price' => 50.00]);
    
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 50.00,
    ]);
    
    expect($orderItem->total)->toBe(100.00);
    expect($orderItem->order->id)->toBe($order->id);
    expect($orderItem->product->id)->toBe($product->id);
});

it('validates product review ratings', function () {
    $product = Product::factory()->create();
    $user = User::factory()->create();
    
    expect(fn() => \App\Models\Review::create([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'reviewer_name' => 'Test User',
        'reviewer_email' => 'test@example.com',
        'title' => 'Test Review',
        'content' => 'Test content',
        'rating' => 6, // Invalid
    ]))->toThrow(\InvalidArgumentException::class);
    
    // Valid rating should work
    $review = \App\Models\Review::create([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'reviewer_name' => 'Test User',
        'reviewer_email' => 'test@example.com',
        'title' => 'Test Review',
        'content' => 'Test content',
        'rating' => 5,
    ]);
    
    expect($review->rating)->toBe(5);
});

it('can approve and reject reviews', function () {
    $review = \App\Models\Review::factory()->create(['is_approved' => false]);
    
    // Test approval
    $review->approve();
    expect($review->fresh()->is_approved)->toBeTrue();
    expect($review->fresh()->approved_at)->not()->toBeNull();
    
    // Test rejection
    $review->reject();
    expect($review->fresh()->is_approved)->toBeFalse();
    expect($review->fresh()->rejected_at)->not()->toBeNull();
});

it('can filter products by various criteria', function () {
    $brand = \App\Models\Brand::factory()->create();
    $category = \App\Models\Category::factory()->create();
    
    $product1 = Product::factory()->create([
        'brand_id' => $brand->id,
        'price' => 100.00,
        'is_featured' => true,
        'stock_quantity' => 10,
    ]);
    $product1->categories()->attach($category->id);
    
    $product2 = Product::factory()->create([
        'price' => 200.00,
        'is_featured' => false,
        'stock_quantity' => 0,
    ]);
    
    // Test featured products
    $featuredProducts = Product::where('is_featured', true)->get();
    expect($featuredProducts)->toHaveCount(1);
    expect($featuredProducts->first()->id)->toBe($product1->id);
    
    // Test price range
    $expensiveProducts = Product::where('price', '>', 150)->get();
    expect($expensiveProducts)->toHaveCount(1);
    expect($expensiveProducts->first()->id)->toBe($product2->id);
    
    // Test in stock
    $inStockProducts = Product::where('stock_quantity', '>', 0)->get();
    expect($inStockProducts)->toHaveCount(1);
    expect($inStockProducts->first()->id)->toBe($product1->id);
});
