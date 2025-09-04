<?php declare(strict_types=1);

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Livewire;

beforeEach(function () {
    // Create permissions
    $permissions = [
        'view_admin_panel', 'view_any_product', 'create_product', 'edit_product', 'delete_product',
        'view_any_order', 'create_order', 'edit_order', 'view_any_user', 'impersonate_users',
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

it('can access user impersonation page', function () {
    $response = $this->actingAs($this->admin)->get('/admin/user-impersonation');
    
    $response->assertOk();
    $response->assertSee('User Management');
});

it('can access system monitoring page', function () {
    $response = $this->actingAs($this->admin)->get('/admin/system-monitoring');
    
    $response->assertOk();
    $response->assertSee('System Monitoring');
});

it('can access inventory management page', function () {
    $response = $this->actingAs($this->admin)->get('/admin/inventory-management');
    
    $response->assertOk();
    $response->assertSee('Inventory Management');
});

it('can access advanced reports page', function () {
    $response = $this->actingAs($this->admin)->get('/admin/advanced-reports');
    
    $response->assertOk();
    $response->assertSee('Advanced Reports');
});

it('can impersonate users', function () {
    $customer = User::factory()->create([
        'name' => 'Test Customer',
        'email' => 'customer@test.com',
        'is_admin' => false,
        'is_active' => true,
    ]);

    Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\UserImpersonation::class)
        ->callTableAction('impersonate', $customer);

    expect(auth()->id())->toBe($customer->id);
    expect(session('impersonate.original_user_id'))->toBe($this->admin->id);
});

it('can view inventory statistics', function () {
    // Create test products with different stock levels
    Product::factory()->create(['stock_quantity' => 50]); // Good stock
    Product::factory()->create(['stock_quantity' => 5]);  // Low stock
    Product::factory()->create(['stock_quantity' => 0]);  // Out of stock

    $page = Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\InventoryManagement::class);

    $page->assertSee('1'); // Should see counts in the overview
});

it('can update product stock through inventory management', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\InventoryManagement::class)
        ->callTableAction('update_stock', $product, [
            'stock_quantity' => 25,
            'low_stock_threshold' => 5,
        ]);

    expect($product->fresh()->stock_quantity)->toBe(25);
    expect($product->fresh()->low_stock_threshold)->toBe(5);
});

it('can perform bulk stock updates', function () {
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

it('can send notifications to users', function () {
    $customer = User::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(\App\Filament\Pages\UserImpersonation::class)
        ->callTableAction('send_notification', $customer, [
            'title' => 'Test Notification',
            'message' => 'This is a test message',
            'type' => 'info',
        ]);

    expect($customer->notifications)->toHaveCount(1);
    expect($customer->notifications->first()->data['title'])->toBe('Test Notification');
});

it('validates admin access to advanced features', function () {
    $regularUser = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($regularUser)->get('/admin/system-monitoring');
    $response->assertForbidden();

    $response = $this->actingAs($regularUser)->get('/admin/user-impersonation');
    $response->assertForbidden();
});
