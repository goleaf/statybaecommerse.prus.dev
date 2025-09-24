<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class WishlistItemResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $regularUser;

    protected UserWishlist $wishlist;

    protected Product $product;

    protected ProductVariant $variant;

    protected WishlistItem $wishlistItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Ensure required permission exists and is granted for Filament topbar checks
        Permission::findOrCreate('view notifications');
        $this->adminUser->givePermissionTo('view notifications');

        $this->regularUser = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $this->wishlist = UserWishlist::factory()->create([
            'user_id' => $this->regularUser->id,
            'name' => 'My Wishlist',
        ]);

        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
        ]);

        $this->variant = ProductVariant::factory()->create([
            'product_id' => $this->product->id,
            'name' => 'Test Variant',
            'price' => 129.99,
        ]);

        $this->wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
            'quantity' => 2,
            'notes' => 'Test notes',
        ]);
    }

    /**
     * @test
     */
    public function admin_can_view_wishlist_items_index_page(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/wishlist-items');

        $response->assertOk();
        $response->assertSee('Wishlist Items');
        $response->assertSee($this->product->name);
        $response->assertSee($this->variant->name);
    }

    /**
     * @test
     */
    public function admin_can_view_wishlist_item_details(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get("/admin/wishlist-items/{$this->wishlistItem->id}");

        $response->assertOk();
        $response->assertSee($this->product->name);
        $response->assertSee($this->variant->name);
        $response->assertSee('2');  // quantity
        $response->assertSee('Test notes');
    }

    /**
     * @test
     */
    public function admin_can_create_wishlist_item(): void
    {
        $this->actingAs($this->adminUser);

        $newProduct = Product::factory()->create([
            'name' => 'New Product',
            'price' => 149.99,
        ]);

        Livewire::test(\App\Filament\Resources\WishlistItemResource\Pages\CreateWishlistItem::class)
            ->fillForm([
                'wishlist_id' => $this->wishlist->id,
                'product_id' => $newProduct->id,
                'quantity' => 3,
                'notes' => 'New wishlist item',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('wishlist_items', [
            'wishlist_id' => $this->wishlist->id,
            'product_id' => $newProduct->id,
            'quantity' => 3,
            'notes' => 'New wishlist item',
        ]);
    }

    /**
     * @test
     */
    public function admin_can_edit_wishlist_item(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\WishlistItemResource\Pages\EditWishlistItem::class, [
            'record' => $this->wishlistItem->id,
        ])
            ->fillForm([
                'quantity' => 5,
                'notes' => 'Updated notes',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('wishlist_items', [
            'id' => $this->wishlistItem->id,
            'quantity' => 5,
            'notes' => 'Updated notes',
        ]);
    }

    /**
     * @test
     */
    public function admin_can_delete_wishlist_item(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\WishlistItemResource\Pages\ListWishlistItems::class)
            ->callTableAction('delete', $this->wishlistItem)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('wishlist_items', [
            'id' => $this->wishlistItem->id,
        ]);
    }

    /**
     * @test
     */
    public function admin_can_filter_wishlist_items_by_wishlist(): void
    {
        $this->actingAs($this->adminUser);

        $anotherWishlist = UserWishlist::factory()->create([
            'user_id' => $this->regularUser->id,
            'name' => 'Another Wishlist',
        ]);

        WishlistItem::factory()->create([
            'wishlist_id' => $anotherWishlist->id,
            'product_id' => $this->product->id,
        ]);

        Livewire::test(\App\Filament\Resources\WishlistItemResource\Pages\ListWishlistItems::class)
            ->filterTable('wishlist_id', $this->wishlist->id)
            ->assertCanSeeTableRecords([$this->wishlistItem])
            ->assertCanNotSeeTableRecords(
                WishlistItem::where('wishlist_id', $anotherWishlist->id)->get()
            );
    }

    /**
     * @test
     */
    public function admin_can_filter_wishlist_items_by_product(): void
    {
        $this->actingAs($this->adminUser);

        $anotherProduct = Product::factory()->create([
            'name' => 'Another Product',
        ]);

        WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id' => $anotherProduct->id,
        ]);

        Livewire::test(\App\Filament\Resources\WishlistItemResource\Pages\ListWishlistItems::class)
            ->filterTable('product_id', $this->product->id)
            ->assertCanSeeTableRecords([$this->wishlistItem])
            ->assertCanNotSeeTableRecords(
                WishlistItem::where('product_id', $anotherProduct->id)->get()
            );
    }

    /**
     * @test
     */
    public function admin_can_filter_wishlist_items_by_variant(): void
    {
        $this->actingAs($this->adminUser);

        $anotherVariant = ProductVariant::factory()->create([
            'product_id' => $this->product->id,
            'name' => 'Another Variant',
        ]);

        $anotherWishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id' => $this->product->id,
            'variant_id' => $anotherVariant->id,
        ]);

        Livewire::test(\App\Filament\Resources\WishlistItemResource\Pages\ListWishlistItems::class)
            ->filterTable('variant_id', $this->variant->id)
            ->assertCanSeeTableRecords([$this->wishlistItem])
            ->assertCanNotSeeTableRecords([$anotherWishlistItem]);
    }

    /**
     * @test
     */
    public function admin_can_filter_wishlist_items_by_has_variant(): void
    {
        $this->actingAs($this->adminUser);

        // Create item without variant
        $itemWithoutVariant = WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id' => $this->product->id,
            'variant_id' => null,
        ]);

        Livewire::test(\App\Filament\Resources\WishlistItemResource\Pages\ListWishlistItems::class)
            ->filterTable('has_variant', true)
            ->assertCanSeeTableRecords([$this->wishlistItem])
            ->assertCanNotSeeTableRecords([$itemWithoutVariant]);
    }

    /**
     * @test
     */
    public function admin_can_filter_wishlist_items_by_user(): void
    {
        $this->actingAs($this->adminUser);

        $anotherUser = User::factory()->create();
        $anotherWishlist = UserWishlist::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        WishlistItem::factory()->create([
            'wishlist_id' => $anotherWishlist->id,
            'product_id' => $this->product->id,
        ]);

        Livewire::test(\App\Filament\Resources\WishlistItemResource\Pages\ListWishlistItems::class)
            ->filterTable('user_id', $this->regularUser->id)
            ->assertCanSeeTableRecords([$this->wishlistItem])
            ->assertCanNotSeeTableRecords(
                WishlistItem::whereHas('wishlist', fn($q) => $q->where('user_id', $anotherUser->id))->get()
            );
    }

    /**
     * @test
     */
    public function admin_can_bulk_delete_wishlist_items(): void
    {
        $this->actingAs($this->adminUser);

        $anotherWishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id' => $this->product->id,
        ]);

        Livewire::test(\App\Filament\Resources\WishlistItemResource\Pages\ListWishlistItems::class)
            ->callTableBulkAction('delete', [$this->wishlistItem, $anotherWishlistItem])
            ->assertHasNoTableBulkActionErrors();

        $this->assertDatabaseMissing('wishlist_items', [
            'id' => $this->wishlistItem->id,
        ]);
        $this->assertDatabaseMissing('wishlist_items', [
            'id' => $anotherWishlistItem->id,
        ]);
    }

    /**
     * @test
     */
    public function wishlist_item_displays_correct_current_price(): void
    {
        $this->actingAs($this->adminUser);

        // Test with variant price
        $this->assertEquals(129.99, $this->wishlistItem->current_price);
        $this->assertEquals('€129.99', $this->wishlistItem->formatted_current_price);
    }

    /**
     * @test
     */
    public function wishlist_item_displays_correct_display_name(): void
    {
        $this->actingAs($this->adminUser);

        $expectedName = $this->product->name . ' - ' . $this->variant->name;
        $this->assertEquals($expectedName, $this->wishlistItem->display_name);
    }

    /**
     * @test
     */
    public function wishlist_item_without_variant_uses_product_price(): void
    {
        $this->actingAs($this->adminUser);

        $itemWithoutVariant = WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id' => $this->product->id,
            'variant_id' => null,
        ]);

        $this->assertEquals(99.99, $itemWithoutVariant->current_price);
        $this->assertEquals($this->product->name, $itemWithoutVariant->display_name);
    }

    /**
     * @test
     */
    public function wishlist_item_quantity_validation_works(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\WishlistItemResource\Pages\CreateWishlistItem::class)
            ->fillForm([
                'wishlist_id' => $this->wishlist->id,
                'product_id' => $this->product->id,
                'quantity' => 0,  // Invalid quantity
            ])
            ->call('create')
            ->assertHasFormErrors(['quantity']);
    }

    /**
     * @test
     */
    public function wishlist_item_requires_wishlist_and_product(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\WishlistItemResource\Pages\CreateWishlistItem::class)
            ->fillForm([
                'quantity' => 1,
            ])
            ->call('create')
            ->assertHasFormErrors(['wishlist_id', 'product_id']);
    }
}
