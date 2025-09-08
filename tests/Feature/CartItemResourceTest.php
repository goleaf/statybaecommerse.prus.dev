<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CartItemResource;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class CartItemResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private ProductVariant $variant;
    private CartItem $cartItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
            'status' => 'published',
        ]);
        $this->variant = ProductVariant::factory()->create([
            'product_id' => $this->product->id,
            'name' => 'Test Variant',
            'price' => 89.99,
        ]);
        $this->cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
            'session_id' => 'test-session-123',
            'quantity' => 2,
            'unit_price' => 89.99,
            'total_price' => 179.98,
            'product_snapshot' => [
                'name' => 'Test Product',
                'price' => 89.99,
                'sku' => 'TEST-SKU',
            ],
        ]);
    }

    public function test_cart_item_resource_can_render_list_page(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->assertSuccessful();
    }

    public function test_cart_item_resource_can_list_cart_items(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->assertCanSeeTableRecords([$this->cartItem]);
    }

    public function test_cart_item_resource_can_render_create_page(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CartItemResource\Pages\CreateCartItem::class)
            ->assertSuccessful();
    }

    public function test_cart_item_resource_can_create_cart_item(): void
    {
        $this->actingAs($this->user);

        $newCartItemData = [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
            'session_id' => 'new-session-456',
            'quantity' => 3,
            'unit_price' => 99.99,
            'total_price' => 299.97,
        ];

        Livewire::test(CartItemResource\Pages\CreateCartItem::class)
            ->fillForm($newCartItemData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('cart_items', $newCartItemData);
    }

    public function test_cart_item_resource_can_validate_required_fields(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CartItemResource\Pages\CreateCartItem::class)
            ->fillForm([
                'session_id' => '',
                'product_id' => null,
                'quantity' => null,
                'unit_price' => null,
                'total_price' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'product_id' => 'required',
                'quantity' => 'required',
                'unit_price' => 'required',
                'total_price' => 'required',
            ]);
    }

    public function test_cart_item_resource_can_render_view_page(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CartItemResource\Pages\ViewCartItem::class, [
            'record' => $this->cartItem->getRouteKey(),
        ])
            ->assertSuccessful();
    }

    public function test_cart_item_resource_can_render_edit_page(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CartItemResource\Pages\EditCartItem::class, [
            'record' => $this->cartItem->getRouteKey(),
        ])
            ->assertSuccessful();
    }

    public function test_cart_item_resource_can_retrieve_data_for_edit(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CartItemResource\Pages\EditCartItem::class, [
            'record' => $this->cartItem->getRouteKey(),
        ])
            ->assertFormSet([
                'user_id' => $this->cartItem->user_id,
                'product_id' => $this->cartItem->product_id,
                'variant_id' => $this->cartItem->variant_id,
                'session_id' => $this->cartItem->session_id,
                'quantity' => $this->cartItem->quantity,
                'unit_price' => $this->cartItem->unit_price,
                'total_price' => $this->cartItem->total_price,
            ]);
    }

    public function test_cart_item_resource_can_save_edited_data(): void
    {
        $this->actingAs($this->user);

        $newData = [
            'quantity' => 5,
            'unit_price' => 79.99,
            'total_price' => 399.95,
        ];

        Livewire::test(CartItemResource\Pages\EditCartItem::class, [
            'record' => $this->cartItem->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        expect($this->cartItem->fresh())
            ->quantity
            ->toBe(5)
            ->unit_price
            ->toBe('79.99')
            ->total_price
            ->toBe('399.95');
    }

    public function test_cart_item_resource_can_delete_cart_item(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CartItemResource\Pages\EditCartItem::class, [
            'record' => $this->cartItem->getRouteKey(),
        ])
            ->callAction('delete');

        $this->assertSoftDeleted($this->cartItem);
    }

    public function test_cart_item_resource_can_filter_by_user_type(): void
    {
        $this->actingAs($this->user);

        // Create a guest cart item
        $guestCartItem = CartItem::factory()->create([
            'user_id' => null,
            'session_id' => 'guest-session',
            'product_id' => $this->product->id,
        ]);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->filterTable('has_user')
            ->assertCanSeeTableRecords([$this->cartItem])
            ->assertCanNotSeeTableRecords([$guestCartItem]);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->filterTable('guest_only')
            ->assertCanSeeTableRecords([$guestCartItem])
            ->assertCanNotSeeTableRecords([$this->cartItem]);
    }

    public function test_cart_item_resource_can_filter_by_date(): void
    {
        $this->actingAs($this->user);

        // Create an old cart item
        $oldCartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'created_at' => now()->subDays(2),
        ]);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->filterTable('created_today')
            ->assertCanSeeTableRecords([$this->cartItem])
            ->assertCanNotSeeTableRecords([$oldCartItem]);
    }

    public function test_cart_item_resource_table_actions_exist(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->assertTableActionExists('view', $this->cartItem)
            ->assertTableActionExists('edit', $this->cartItem)
            ->assertTableActionExists('delete', $this->cartItem);
    }

    public function test_cart_item_resource_can_perform_view_action(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->callTableAction('view', $this->cartItem)
            ->assertSuccessful();
    }

    public function test_cart_item_resource_can_perform_edit_action(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->callTableAction('edit', $this->cartItem)
            ->assertSuccessful();
    }

    public function test_cart_item_resource_can_perform_delete_action(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->callTableAction('delete', $this->cartItem);

        $this->assertSoftDeleted($this->cartItem);
    }

    public function test_cart_item_resource_bulk_actions_exist(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->assertTableBulkActionExists('delete');
    }

    public function test_cart_item_resource_can_perform_bulk_delete(): void
    {
        $this->actingAs($this->user);

        $cartItem2 = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->selectTableRecords([$this->cartItem, $cartItem2])
            ->callTableBulkAction('delete');

        $this->assertSoftDeleted($this->cartItem);
        $this->assertSoftDeleted($cartItem2);
    }

    public function test_cart_item_resource_can_clear_old_cart_items(): void
    {
        $this->actingAs($this->user);

        // Create old cart items
        $oldCartItem1 = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'created_at' => now()->subDays(8),
        ]);

        $oldCartItem2 = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'created_at' => now()->subDays(10),
        ]);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->selectTableRecords([$oldCartItem1, $oldCartItem2])
            ->callTableBulkAction('clear_old_carts');

        $this->assertDatabaseMissing('cart_items', ['id' => $oldCartItem1->id]);
        $this->assertDatabaseMissing('cart_items', ['id' => $oldCartItem2->id]);
        $this->assertDatabaseHas('cart_items', ['id' => $this->cartItem->id]);
    }

    public function test_cart_item_resource_displays_correct_columns(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->assertCanRenderTableColumn('id')
            ->assertCanRenderTableColumn('user.name')
            ->assertCanRenderTableColumn('session_id')
            ->assertCanRenderTableColumn('product.name')
            ->assertCanRenderTableColumn('variant.name')
            ->assertCanRenderTableColumn('quantity')
            ->assertCanRenderTableColumn('unit_price')
            ->assertCanRenderTableColumn('total_price')
            ->assertCanRenderTableColumn('created_at');
    }

    public function test_cart_item_resource_can_search_records(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->searchTable($this->user->name)
            ->assertCanSeeTableRecords([$this->cartItem]);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->searchTable($this->product->name)
            ->assertCanSeeTableRecords([$this->cartItem]);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->searchTable('nonexistent-search-term')
            ->assertCanNotSeeTableRecords([$this->cartItem]);
    }

    public function test_cart_item_resource_can_sort_records(): void
    {
        $this->actingAs($this->user);

        $cartItem2 = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'created_at' => now()->subHour(),
        ]);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$this->cartItem, $cartItem2], inOrder: true);

        Livewire::test(CartItemResource\Pages\ListCartItems::class)
            ->sortTable('quantity', 'asc')
            ->assertCanSeeTableRecords([$cartItem2, $this->cartItem], inOrder: true);
    }
}
