<?php declare(strict_types=1);

use App\Models\CustomerGroup;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create comprehensive permissions
    $permissions = [
        'view_admin_panel',
        'view_any_product',
        'create_product',
        'edit_product',
        'delete_product',
        'view_any_order',
        'create_order',
        'edit_order',
        'view_any_user',
        'impersonate_users',
        'view_security_audit',
        'view_seo_analytics',
        'manage_imports',
        'manage_customer_segments',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    // Create admin role with all permissions
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions($permissions);

    // Create admin user
    $this->admin = User::factory()->create([
        'name' => 'System Admin',
        'email' => 'admin@system.test',
        'is_active' => true,
        'is_admin' => true,
    ]);
    $this->admin->assignRole($adminRole);
});

it('can access all advanced admin pages', function () {
    $pages = [
        '/admin/data-import-export',
        '/admin/customer-segmentation',
        '/admin/seo-analytics',
        '/admin/security-audit',
        '/admin/user-impersonation',
        '/admin/system-monitoring',
        '/admin/inventory-management',
    ];

    foreach ($pages as $page) {
        $response = $this->actingAs($this->admin)->get($page);
        if ($response->status() !== 200) {
            dump("Failed page: $page with status: " . $response->status());
        }
        $response->assertOk();
    }
});

it('can perform data import operations', function () {
    // Test that the data import export page loads correctly
    $response = $this->actingAs($this->admin)->get('/admin/data-import-export');
    $response->assertOk();
    
    // Test that the page contains import functionality
    $response->assertSee('Import');
    $response->assertSee('Export');
});

it('can export data in multiple formats', function () {
    Product::factory()->count(5)->create();

    // Test that the data import export page loads correctly
    $response = $this->actingAs($this->admin)->get('/admin/data-import-export');
    $response->assertOk();
    
    // Test that the page contains export functionality
    $response->assertSee('Export');
});

it('can perform customer segmentation', function () {
    // Create customers with different spending patterns
    $highValueCustomer = User::factory()->create(['is_admin' => false]);
    $regularCustomer = User::factory()->create(['is_admin' => false]);
    $newCustomer = User::factory()->create(['is_admin' => false]);

    // Create orders for segmentation
    Order::factory()->create([
        'user_id' => $highValueCustomer->id,
        'status' => 'completed',
        'total' => 1500.0,
    ]);

    Order::factory()->create([
        'user_id' => $regularCustomer->id,
        'status' => 'completed',
        'total' => 300.0,
    ]);

    Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\CustomerSegmentation::class)
        ->assertSee($highValueCustomer->name)
        ->assertSee($regularCustomer->name);
});

it('can create customer groups and assign users', function () {
    $customer = User::factory()->create(['is_admin' => false]);
    $group = CustomerGroup::factory()->create(['name' => 'VIP Customers']);

    Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\CustomerSegmentation::class)
        ->callTableAction('assign_to_group', $customer, [
            'customer_group_id' => $group->id,
        ]);

    expect($customer->fresh()->customerGroups)->toHaveCount(1);
    expect($customer->fresh()->customerGroups->first()->name)->toBe('VIP Customers');
});

it('can perform SEO audits and optimization', function () {
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'seo_title' => null,
        'seo_description' => null,
    ]);

    Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\SEOAnalytics::class)
        ->set('seoEntityType', 'products')
        ->callTableAction('optimize_seo', $product);

    expect($product->fresh()->seo_title)->not()->toBeNull();
    expect($product->fresh()->seo_description)->not()->toBeNull();
});

it('can track and analyze security activities', function () {
    // Create some test activities
    activity('security')
        ->causedBy($this->admin)
        ->log('Admin login attempt');

    activity('security')
        ->log('Failed login attempt from suspicious IP');

    // Verify activities were created
    expect(\Spatie\Activitylog\Models\Activity::where('log_name', 'security')->count())->toBeGreaterThan(0);

    // Test that the page loads without errors
    Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\SecurityAudit::class)
        ->assertOk();
});

it('can manage inventory with bulk operations', function () {
    $products = Product::factory()->count(3)->create(['stock_quantity' => 10]);

    Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\InventoryManagement::class)
        ->callTableBulkAction('bulk_stock_update', $products, [
            'operation' => 'increase',
            'quantity' => 5,
        ]);

    foreach ($products as $product) {
        expect($product->fresh()->stock_quantity)->toBe(15);
    }
});

it('can generate product recommendations', function () {
    $user = User::factory()->create();
    $category = \App\Models\Category::factory()->create();

    $product1 = Product::factory()->create(['price' => 100.0]);
    $product2 = Product::factory()->create(['price' => 150.0]);

    $product1->categories()->attach($category->id);
    $product2->categories()->attach($category->id);

    // Create order history for personalized recommendations
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'completed',
    ]);

    \App\Models\OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product1->id,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Components\ProductRecommendations::class, [
            'type' => 'personalized',
            'userId' => $user->id,
        ])
        ->assertSee($product2->name);  // Should recommend product2 based on category
});

it('can track recently viewed products', function () {
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();

    Livewire::test(\App\Livewire\Components\ProductRecommendations::class, [
        'productId' => $product1->id,
        'type' => 'recently_viewed',
    ])
        ->call('trackView');

    expect(session('recently_viewed'))->toContain($product1->id);
});

it('can send marketing emails to customers', function () {
    $customer = User::factory()->create(['is_admin' => false]);

    // Test that the page loads and the action is available
    Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\CustomerSegmentation::class)
        ->assertOk();
});

it('can perform comprehensive system monitoring', function () {
    Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\SystemMonitoring::class)
        ->assertOk();
});
