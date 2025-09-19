<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\WishlistItemResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WishlistItemResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
        $this->variant = ProductVariant::factory()->create(['product_id' => $this->product->id]);
        $this->wishlist = UserWishlist::factory()->create(['user_id' => $this->user->id]);
        $this->wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
        ]);
    }

    public function test_can_list_wishlist_items(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->assertCanSeeTableRecords([$this->wishlistItem])
            ->assertCanSeeTableColumns([
                'id',
                'wishlist.name',
                'wishlist.user.name',
                'product.name',
                'variant.name',
                'quantity',
                'display_name',
                'current_price',
                'notes',
                'created_at',
            ]);
    }

    public function test_can_create_wishlist_item(): void
    {
        $this->actingAs($this->user);

        $newWishlistItemData = [
            'wishlist_id' => $this->wishlist->id,
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
            'quantity' => 2,
            'notes' => 'Test notes',
        ];

        Livewire::test(CreateRecord::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->fillForm($newWishlistItemData)
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('wishlist_items', [
            'wishlist_id' => $newWishlistItemData['wishlist_id'],
            'product_id' => $newWishlistItemData['product_id'],
            'variant_id' => $newWishlistItemData['variant_id'],
            'quantity' => $newWishlistItemData['quantity'],
            'notes' => $newWishlistItemData['notes'],
        ]);
    }

    public function test_can_edit_wishlist_item(): void
    {
        $this->actingAs($this->user);

        $updatedData = [
            'quantity' => 5,
            'notes' => 'Updated notes',
        ];

        Livewire::test(EditRecord::class, [
            'resource' => WishlistItemResource::class,
            'record' => $this->wishlistItem->id,
        ])
            ->fillForm($updatedData)
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('wishlist_items', [
            'id' => $this->wishlistItem->id,
            'quantity' => $updatedData['quantity'],
            'notes' => $updatedData['notes'],
        ]);
    }

    public function test_can_view_wishlist_item(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ViewRecord::class, [
            'resource' => WishlistItemResource::class,
            'record' => $this->wishlistItem->id,
        ])
            ->assertOk();
    }

    public function test_can_filter_wishlist_items_by_wishlist(): void
    {
        $this->actingAs($this->user);

        $anotherWishlist = UserWishlist::factory()->create(['user_id' => $this->user->id]);
        $anotherWishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $anotherWishlist->id,
            'product_id' => $this->product->id,
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->filterTable('wishlist_id', $this->wishlist->id)
            ->assertCanSeeTableRecords([$this->wishlistItem])
            ->assertCanNotSeeTableRecords([$anotherWishlistItem]);
    }

    public function test_can_filter_wishlist_items_by_product(): void
    {
        $this->actingAs($this->user);

        $anotherProduct = Product::factory()->create();
        $anotherWishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id' => $anotherProduct->id,
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->filterTable('product_id', $this->product->id)
            ->assertCanSeeTableRecords([$this->wishlistItem])
            ->assertCanNotSeeTableRecords([$anotherWishlistItem]);
    }

    public function test_can_filter_wishlist_items_by_variant(): void
    {
        $this->actingAs($this->user);

        $anotherVariant = ProductVariant::factory()->create(['product_id' => $this->product->id]);
        $anotherWishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id' => $this->product->id,
            'variant_id' => $anotherVariant->id,
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->filterTable('variant_id', $this->variant->id)
            ->assertCanSeeTableRecords([$this->wishlistItem])
            ->assertCanNotSeeTableRecords([$anotherWishlistItem]);
    }

    public function test_can_filter_wishlist_items_by_has_variant(): void
    {
        $this->actingAs($this->user);

        $wishlistItemWithoutVariant = WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id' => $this->product->id,
            'variant_id' => null,
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->filterTable('has_variant', true)
            ->assertCanSeeTableRecords([$this->wishlistItem])
            ->assertCanNotSeeTableRecords([$wishlistItemWithoutVariant]);
    }

    public function test_can_filter_wishlist_items_by_user(): void
    {
        $this->actingAs($this->user);

        $anotherUser = User::factory()->create();
        $anotherWishlist = UserWishlist::factory()->create(['user_id' => $anotherUser->id]);
        $anotherWishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $anotherWishlist->id,
            'product_id' => $this->product->id,
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->filterTable('user_id', $this->user->id)
            ->assertCanSeeTableRecords([$this->wishlistItem])
            ->assertCanNotSeeTableRecords([$anotherWishlistItem]);
    }

    public function test_can_search_wishlist_items(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->searchTable($this->product->name)
            ->assertCanSeeTableRecords([$this->wishlistItem]);
    }

    public function test_can_sort_wishlist_items(): void
    {
        $this->actingAs($this->user);

        $anotherWishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id' => $this->product->id,
            'created_at' => now()->addDay(),
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$anotherWishlistItem, $this->wishlistItem]);
    }

    public function test_can_bulk_delete_wishlist_items(): void
    {
        $this->actingAs($this->user);

        $anotherWishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id' => $this->product->id,
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->callTableBulkAction('delete', [$this->wishlistItem, $anotherWishlistItem])
            ->assertHasNoTableBulkActionErrors();

        $this->assertDatabaseMissing('wishlist_items', [
            'id' => $this->wishlistItem->id,
        ]);
        $this->assertDatabaseMissing('wishlist_items', [
            'id' => $anotherWishlistItem->id,
        ]);
    }

    public function test_can_move_wishlist_item_to_cart(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->callTableAction('move_to_cart', $this->wishlistItem)
            ->assertNotified();
    }

    public function test_can_bulk_move_wishlist_items_to_cart(): void
    {
        $this->actingAs($this->user);

        $anotherWishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id' => $this->product->id,
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->callTableBulkAction('move_to_cart', [$this->wishlistItem, $anotherWishlistItem])
            ->assertNotified();
    }

    public function test_form_validation_works(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateRecord::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->fillForm([
                'wishlist_id' => null,
                'product_id' => null,
                'quantity' => 0,
            ])
            ->call('create')
            ->assertHasFormErrors(['wishlist_id', 'product_id', 'quantity']);
    }

    public function test_can_create_wishlist_during_wishlist_item_creation(): void
    {
        $this->actingAs($this->user);

        $newWishlistData = [
            'name' => 'New Wishlist',
            'description' => 'New wishlist description',
            'is_public' => true,
            'is_default' => false,
        ];

        Livewire::test(CreateRecord::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->fillForm([
                'wishlist_id' => null,  // This should trigger the create option
            ])
            ->assertFormExists()
            ->assertCanSeeFormField('wishlist_id');
    }

    public function test_variant_field_is_visible_when_product_has_variants(): void
    {
        $this->actingAs($this->user);

        // Ensure product has variants
        $this->product->variants()->save($this->variant);

        Livewire::test(CreateRecord::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->fillForm([
                'product_id' => $this->product->id,
            ])
            ->assertFormExists()
            ->assertCanSeeFormField('variant_id');
    }

    public function test_variant_field_is_hidden_when_product_has_no_variants(): void
    {
        $this->actingAs($this->user);

        $productWithoutVariants = Product::factory()->create();

        Livewire::test(CreateRecord::class, [
            'resource' => WishlistItemResource::class,
        ])
            ->fillForm([
                'product_id' => $productWithoutVariants->id,
            ])
            ->assertFormExists()
            ->assertCanNotSeeFormField('variant_id');
    }

    public function test_display_name_is_calculated_correctly(): void
    {
        $this->actingAs($this->user);

        $this->assertEquals(
            $this->product->name . ' - ' . $this->variant->name,
            $this->wishlistItem->display_name
        );
    }

    public function test_current_price_is_calculated_correctly(): void
    {
        $this->actingAs($this->user);

        $this->assertEquals(
            $this->variant->price,
            $this->wishlistItem->current_price
        );
    }

    public function test_formatted_current_price_is_calculated_correctly(): void
    {
        $this->actingAs($this->user);

        $this->assertIsString($this->wishlistItem->formatted_current_price);
    }

    public function test_wishlist_item_relationships_work(): void
    {
        $this->actingAs($this->user);

        $this->assertInstanceOf(UserWishlist::class, $this->wishlistItem->wishlist);
        $this->assertInstanceOf(Product::class, $this->wishlistItem->product);
        $this->assertInstanceOf(ProductVariant::class, $this->wishlistItem->variant);
    }

    public function test_wishlist_item_scopes_work(): void
    {
        $this->actingAs($this->user);

        // Test scopeForUser
        $userWishlistItems = WishlistItem::forUser($this->user->id)->get();
        $this->assertTrue($userWishlistItems->contains($this->wishlistItem));

        // Test scopeForProduct
        $productWishlistItems = WishlistItem::forProduct($this->product->id)->get();
        $this->assertTrue($productWishlistItems->contains($this->wishlistItem));

        // Test scopeRecent
        $recentWishlistItems = WishlistItem::recent(7)->get();
        $this->assertTrue($recentWishlistItems->contains($this->wishlistItem));
    }
}
