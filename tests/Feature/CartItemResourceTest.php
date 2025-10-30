<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CartItemResource\Pages\CreateCartItem;
use App\Filament\Resources\CartItemResource\Pages\EditCartItem;
use App\Filament\Resources\CartItemResource\Pages\ListCartItems;
use App\Filament\Resources\CartItemResource\Pages\ViewCartItem;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CartItemResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure the Filament admin panel is registered before interacting with resource pages.
        $this->resolveAdminPanel();

        $this->adminUser = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_list_cart_items(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        CartItem::factory()->create([
            'user_id'     => $user->id,
            'product_id'  => $product->id,
            'quantity'    => 2,
            'unit_price'  => 29.99,
            'total_price' => 59.98,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords(CartItem::all())
            ->assertCanSeeTableColumns([
                'user.name',
                'product.name',
                'product.sku',
                'quantity',
                'unit_price',
                'total_price',
                'created_at',
            ]);
    }

    public function test_can_create_cart_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 25.99]);

        Livewire::actingAs($this->adminUser)
            ->test(CreateCartItem::class)
            ->fillForm([
                'user_id'         => $user->id,
                'product_id'      => $product->id,
                'quantity'        => 3,
                'unit_price'      => 25.99,
                'discount_amount' => 5.0,
                'session_id'      => 'session_123',
                'notes'           => 'Test cart item',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 3,
            'unit_price' => 25.99,
        ]);
    }

    public function test_can_edit_cart_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 1,
            'unit_price' => 20.0,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(EditCartItem::class, ['record' => $cartItem->getRouteKey()])
            ->fillForm([
                'quantity'        => 5,
                'unit_price'      => 25.0,
                'discount_amount' => 10.0,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $cartItem->refresh();
        $this->assertEquals(5, $cartItem->quantity);
        $this->assertEquals(25.0, $cartItem->unit_price);
        $this->assertEquals(10.0, $cartItem->discount_amount);
    }

    public function test_can_view_cart_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 2,
            'unit_price' => 15.5,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ViewCartItem::class, ['record' => $cartItem->getRouteKey()])
            ->assertHasNoErrors();
    }

    public function test_can_filter_by_user(): void
    {
        $user1 = User::factory()->create(['name' => 'User 1']);
        $user2 = User::factory()->create(['name' => 'User 2']);
        $product = Product::factory()->create();

        $cartItem1 = CartItem::factory()->create([
            'user_id'    => $user1->id,
            'product_id' => $product->id,
        ]);

        $cartItem2 = CartItem::factory()->create([
            'user_id'    => $user2->id,
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->filterTable('user_id', $user1->id)
            ->assertCanSeeTableRecords([$cartItem1])
            ->assertCanNotSeeTableRecords([$cartItem2]);
    }

    public function test_can_filter_by_product(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['name' => 'Product 1']);
        $product2 = Product::factory()->create(['name' => 'Product 2']);

        $cartItem1 = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product1->id,
        ]);

        $cartItem2 = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product2->id,
        ]);

        $component = Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class);

        $component->call('loadTable');
        $component->filterTable('product_id', (string) $product1->id);
        file_put_contents(storage_path('logs/filter-state.json'), json_encode($component->instance()->tableFilters));
        $component->call('loadTable');

        // Confirm the filtered table includes the desired product while excluding the rest.
        $query = $component->instance()->getFilteredTableQuery();

        $this->assertTrue($query->clone()->whereKey($cartItem1->getKey())->exists());
        $this->assertFalse($query->clone()->whereKey($cartItem2->getKey())->exists());
    }

    public function test_can_filter_by_product_variant(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant1 = ProductVariant::factory()->create(['product_id' => $product->id]);
        $variant2 = ProductVariant::factory()->create(['product_id' => $product->id]);

        $cartItem1 = CartItem::factory()->create([
            'user_id'            => $user->id,
            'product_id'         => $product->id,
            'product_variant_id' => $variant1->id,
        ]);

        $cartItem2 = CartItem::factory()->create([
            'user_id'            => $user->id,
            'product_id'         => $product->id,
            'product_variant_id' => $variant2->id,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->filterTable('product_variant_id', $variant1->id)
            ->assertCanSeeTableRecords([$cartItem1])
            ->assertCanNotSeeTableRecords([$cartItem2]);
    }

    public function test_can_filter_by_restocking_status(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $needsRestocking = CartItem::factory()->create([
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'quantity'         => 1,
            'minimum_quantity' => 5,
        ]);

        $sufficientStock = CartItem::factory()->create([
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'quantity'         => 10,
            'minimum_quantity' => 5,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->filterTable('needs_restocking', true)
            ->assertCanSeeTableRecords([$needsRestocking])
            ->assertCanNotSeeTableRecords([$sufficientStock]);
    }

    public function test_can_filter_by_quantity_range(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $lowQuantity = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $highQuantity = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 10,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->filterTable('quantity_range', [
                'quantity_from' => 1,
                'quantity_to'   => 5,
            ])
            ->assertCanSeeTableRecords([$lowQuantity])
            ->assertCanNotSeeTableRecords([$highQuantity]);
    }

    public function test_can_filter_by_price_range(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $lowPrice = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'unit_price' => 10.0,
        ]);

        $highPrice = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'unit_price' => 50.0,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->filterTable('price_range', [
                'price_from' => 5.0,
                'price_to'   => 25.0,
            ])
            ->assertCanSeeTableRecords([$lowPrice])
            ->assertCanNotSeeTableRecords([$highPrice]);
    }

    public function test_can_update_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->mountTableAction('update_quantity', $cartItem)
            ->setTableActionData([
                'quantity' => 5,
            ])
            ->callMountedTableAction()
            ->assertNotified();

        $cartItem->refresh();
        $this->assertEquals(5, $cartItem->quantity);
    }

    public function test_can_move_to_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->callTableAction('move_to_wishlist', $cartItem)
            ->assertNotified();
    }

    public function test_can_duplicate_cart_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->callTableAction('duplicate', $cartItem)
            ->assertNotified();

        $this->assertDatabaseCount('cart_items', 2);
    }

    public function test_can_bulk_update_quantities(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cartItem1 = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $cartItem2 = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->mountTableBulkAction('update_quantities', [$cartItem1, $cartItem2])
            ->setTableBulkActionData([
                'quantity' => 5,
            ])
            ->callMountedTableBulkAction()
            ->assertNotified();

        $cartItem1->refresh();
        $cartItem2->refresh();
        $this->assertEquals(5, $cartItem1->quantity);
        $this->assertEquals(5, $cartItem2->quantity);
    }

    public function test_can_bulk_move_to_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cartItem1 = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        $cartItem2 = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->callTableBulkAction('move_to_wishlist', [$cartItem1, $cartItem2])
            ->assertNotified();
    }

    public function test_can_clear_old_carts(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $oldCartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'created_at' => now()->subDays(35),
        ]);

        $recentCartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'created_at' => now()->subDays(10),
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->callTableBulkAction('clear_old_carts', [$oldCartItem, $recentCartItem])
            ->assertNotified();

        $this->assertDatabaseMissing('cart_items', ['id' => $oldCartItem->id]);
        $this->assertDatabaseHas('cart_items', ['id' => $recentCartItem->id]);
    }

    public function test_can_export_cart_items(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->callTableBulkAction('export_cart_items', [$cartItem])
            ->assertNotified();
    }

    public function test_quantity_badge_colors(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $lowQuantity = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $mediumQuantity = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 7,
        ]);

        $highQuantity = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 15,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$lowQuantity, $mediumQuantity, $highQuantity]);
    }

    public function test_product_relationship_display(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Test Product', 'sku' => 'TEST-001']);

        $cartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$cartItem]);
    }

    public function test_product_variant_relationship_display(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name'       => 'Test Variant',
        ]);

        $cartItem = CartItem::factory()->create([
            'user_id'            => $user->id,
            'product_id'         => $product->id,
            'product_variant_id' => $variant->id,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$cartItem]);
    }

    public function test_user_relationship_display(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $product = Product::factory()->create();

        $cartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$cartItem]);
    }

    public function test_price_calculations(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cartItem = CartItem::factory()->create([
            'user_id'         => $user->id,
            'product_id'      => $product->id,
            'quantity'        => 3,
            'unit_price'      => 20.0,
            'discount_amount' => 5.0,
            'total_price'     => 55.0,  // (3 * 20.00) - 5.00
        ]);

        $this->assertDatabaseHas('cart_items', [
            'total_price' => 55.0,
        ]);
    }

    public function test_session_id_tracking(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $sessionId = 'session_' . uniqid();

        $cartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'session_id' => $sessionId,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'session_id' => $sessionId,
        ]);
    }

    public function test_attributes_storage(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $attributes = [
            'color'    => 'red',
            'size'     => 'large',
            'material' => 'cotton',
        ];

        $cartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'attributes' => $attributes,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'attributes' => json_encode($attributes),
        ]);
    }

    public function test_product_snapshot_storage(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $snapshot = [
            'name'        => 'Product Name',
            'price'       => 29.99,
            'description' => 'Product Description',
        ];

        $cartItem = CartItem::factory()->create([
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'product_snapshot' => $snapshot,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'product_snapshot' => json_encode($snapshot),
        ]);
    }

    public function test_notes_storage(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $notes = 'Special instructions for this item';

        $cartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'notes'      => $notes,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'notes' => $notes,
        ]);
    }

    public function test_table_sorting(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cartItem1 = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'created_at' => now()->subDay(),
        ]);

        $cartItem2 = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'created_at' => now(),
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$cartItem2, $cartItem1]);
    }

    public function test_search_functionality(): void
    {
        $user = User::factory()->create(['name' => 'Searchable User']);
        $product = Product::factory()->create(['name' => 'Searchable Product']);

        $cartItem = CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListCartItems::class)
            ->call('loadTable')
            ->searchTable('Searchable')
            ->assertCanSeeTableRecords([$cartItem]);
    }
}
