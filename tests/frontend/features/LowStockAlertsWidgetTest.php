<?php

declare(strict_types=1);

use App\Filament\Widgets\LowStockAlertsWidget;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->adminUser = User::factory()->create([
        'email' => 'admin@admin.com',
        'name' => 'Admin User',
    ]);

    // Create role and permissions if they don't exist
    $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);

    // Create all necessary permissions
    $permissions = [
        'view_product',
        'edit_product',
        'create_product',
        'delete_product',
    ];

    foreach ($permissions as $permission) {
        $perm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        $role->givePermissionTo($perm);
    }

    $this->adminUser->assignRole($role);

    actingAs($this->adminUser);
});

it('can render low stock alerts widget', function () {
    livewire(LowStockAlertsWidget::class)
        ->assertSuccessful();
});

it('displays products with low stock', function () {
    // Create products with different stock levels
    $lowStockProduct = Product::factory()->create([
        'name' => 'Low Stock Product',
        'sku' => 'LOW001',
        'is_visible' => true,
        'manage_stock' => true,
        'stock_quantity' => 2,
        'low_stock_threshold' => 5,
    ]);

    $normalStockProduct = Product::factory()->create([
        'name' => 'Normal Stock Product',
        'sku' => 'NORMAL001',
        'is_visible' => true,
        'manage_stock' => true,
        'stock_quantity' => 10,
        'low_stock_threshold' => 5,
    ]);

    livewire(LowStockAlertsWidget::class)
        ->assertCanSeeTableRecords([$lowStockProduct])
        ->assertCanNotSeeTableRecords([$normalStockProduct]);
});

it('can render table columns correctly', function () {
    Product::factory()->create([
        'name' => 'Test Product',
        'sku' => 'TEST001',
        'is_visible' => true,
        'manage_stock' => true,
        'stock_quantity' => 1,
        'low_stock_threshold' => 5,
    ]);

    livewire(LowStockAlertsWidget::class)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('sku')
        ->assertCanRenderTableColumn('stock_quantity')
        ->assertCanRenderTableColumn('low_stock_threshold');
});

it('can perform restock action', function () {
    $product = Product::factory()->create([
        'name' => 'Restock Product',
        'sku' => 'RESTOCK001',
        'is_visible' => true,
        'manage_stock' => true,
        'stock_quantity' => 2,
        'low_stock_threshold' => 5,
    ]);

    livewire(LowStockAlertsWidget::class)
        ->callAction(TestAction::make('restock')->table($product), data: [
            'quantity' => 10,
        ]);

    expect($product->fresh()->stock_quantity)->toBe(12);
});

it('can access edit product action', function () {
    $product = Product::factory()->create([
        'name' => 'Edit Product',
        'sku' => 'EDIT001',
        'is_visible' => true,
        'manage_stock' => true,
        'stock_quantity' => 1,
        'low_stock_threshold' => 5,
    ]);

    livewire(LowStockAlertsWidget::class)
        ->assertActionExists(TestAction::make('edit')->table($product));
});

it('shows correct stock status badges', function () {
    $outOfStockProduct = Product::factory()->create([
        'name' => 'Out of Stock',
        'sku' => 'OUT001',
        'is_visible' => true,
        'manage_stock' => true,
        'stock_quantity' => 0,
        'low_stock_threshold' => 5,
    ]);

    $lowStockProduct = Product::factory()->create([
        'name' => 'Low Stock',
        'sku' => 'LOW001',
        'is_visible' => true,
        'manage_stock' => true,
        'stock_quantity' => 3,
        'low_stock_threshold' => 5,
    ]);

    livewire(LowStockAlertsWidget::class)
        ->assertCanSeeTableRecords([$outOfStockProduct, $lowStockProduct]);
});

it('only shows visible products that manage stock', function () {
    $visibleManagedProduct = Product::factory()->create([
        'name' => 'Visible Managed',
        'is_visible' => true,
        'manage_stock' => true,
        'stock_quantity' => 2,
        'low_stock_threshold' => 5,
    ]);

    $hiddenProduct = Product::factory()->create([
        'name' => 'Hidden Product',
        'is_visible' => false,
        'manage_stock' => true,
        'stock_quantity' => 2,
        'low_stock_threshold' => 5,
    ]);

    $unmanagedProduct = Product::factory()->create([
        'name' => 'Unmanaged Product',
        'is_visible' => true,
        'manage_stock' => false,
        'stock_quantity' => 2,
        'low_stock_threshold' => 5,
    ]);

    livewire(LowStockAlertsWidget::class)
        ->assertCanSeeTableRecords([$visibleManagedProduct])
        ->assertCanNotSeeTableRecords([$hiddenProduct, $unmanagedProduct]);
});

it('sorts products by stock quantity ascending', function () {
    $product1 = Product::factory()->create([
        'name' => 'Product 1',
        'is_visible' => true,
        'manage_stock' => true,
        'stock_quantity' => 3,
        'low_stock_threshold' => 5,
    ]);

    $product2 = Product::factory()->create([
        'name' => 'Product 2',
        'is_visible' => true,
        'manage_stock' => true,
        'stock_quantity' => 1,
        'low_stock_threshold' => 5,
    ]);

    livewire(LowStockAlertsWidget::class)
        ->assertCanSeeTableRecords([$product2, $product1], inOrder: true);
});

it('can search products by name', function () {
    $searchableProduct = Product::factory()->create([
        'name' => 'Searchable Product',
        'is_visible' => true,
        'manage_stock' => true,
        'stock_quantity' => 2,
        'low_stock_threshold' => 5,
    ]);

    $otherProduct = Product::factory()->create([
        'name' => 'Other Product',
        'is_visible' => true,
        'manage_stock' => true,
        'stock_quantity' => 2,
        'low_stock_threshold' => 5,
    ]);

    livewire(LowStockAlertsWidget::class)
        ->searchTable('Searchable')
        ->assertCanSeeTableRecords([$searchableProduct])
        ->assertCanNotSeeTableRecords([$otherProduct]);
});

it('displays correct heading', function () {
    livewire(LowStockAlertsWidget::class)
        ->assertSee(__('admin.widgets.low_stock_alerts'));
});

it('shows empty state when no low stock products', function () {
    Product::factory()->create([
        'name' => 'Well Stocked Product',
        'is_visible' => true,
        'manage_stock' => true,
        'stock_quantity' => 20,
        'low_stock_threshold' => 5,
    ]);

    livewire(LowStockAlertsWidget::class)
        ->assertSee(__('No Low Stock Items'))
        ->assertSee(__('All products are well stocked!'));
});
