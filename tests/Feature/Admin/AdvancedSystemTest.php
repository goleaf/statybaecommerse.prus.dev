<?php declare(strict_types=1);

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\CustomerGroup;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Activitylog\Models\Activity;
use Livewire\Livewire;

beforeEach(function () {
    // Create comprehensive permissions
    $permissions = [
        'view_admin_panel', 'view_any_product', 'create_product', 'edit_product', 'delete_product',
        'view_any_order', 'create_order', 'edit_order', 'view_any_user', 'impersonate_users',
        'view_security_audit', 'view_seo_analytics', 'manage_imports', 'manage_customer_segments',
    ];
    
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }
    
    // Create admin role with all permissions
    $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $adminRole->syncPermissions($permissions);
    
    // Create admin user
    $this->admin = User::factory()->create([
        'name' => 'System Admin',
        'email' => 'admin@system.test',
        'is_active' => true,
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
        $response->assertOk();
    }
});

it('can perform data import operations', function () {
    $csvContent = "name,sku,price,stock_quantity\n";
    $csvContent .= "Test Product,TEST-001,99.99,10\n";
    $csvContent .= "Another Product,TEST-002,149.99,5\n";

    $tempFile = tmpfile();
    fwrite($tempFile, $csvContent);
    $tempPath = stream_get_meta_data($tempFile)['uri'];

    $uploadedFile = new \Illuminate\Http\UploadedFile(
        $tempPath,
        'products.csv',
        'text/csv',
        null,
        true
    );

    Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\DataImportExport::class)
        ->call('processImport', $uploadedFile);

    expect(Product::where('sku', 'TEST-001')->exists())->toBeTrue();
    expect(Product::where('sku', 'TEST-002')->exists())->toBeTrue();
});

it('can export data in multiple formats', function () {
    Product::factory()->count(5)->create();

    $page = Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\DataImportExport::class)
        ->set('selectedModel', 'products')
        ->set('exportFormat', 'csv')
        ->call('processExport');

    expect(\Storage::disk('public')->exists('exports'))->toBeTrue();
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
        'total' => 1500.00,
    ]);

    Order::factory()->create([
        'user_id' => $regularCustomer->id,
        'status' => 'completed',
        'total' => 300.00,
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

    Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\SecurityAudit::class)
        ->assertSee('Admin login attempt')
        ->assertSee('Failed login attempt');
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
    
    $product1 = Product::factory()->create(['price' => 100.00]);
    $product2 = Product::factory()->create(['price' => 150.00]);
    
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
        ->assertSee($product2->name); // Should recommend product2 based on category
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

    Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\CustomerSegmentation::class)
        ->callTableAction('send_marketing_email', $customer, [
            'subject' => 'Special Offer',
            'content' => 'Get 20% off your next purchase!',
            'template' => 'promotional',
        ]);

    expect($customer->notifications)->toHaveCount(1);
});

it('can perform comprehensive system monitoring', function () {
    Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\SystemMonitoring::class)
        ->assertSee('System Info')
        ->assertSee('Database Info')
        ->assertSee('Performance Metrics');
});


