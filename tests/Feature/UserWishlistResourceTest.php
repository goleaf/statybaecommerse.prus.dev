<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\UserWishlistResource;
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

class UserWishlistResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create();
        $this->wishlist = UserWishlist::factory()->create(['user_id' => $this->user->id]);
        $this->wishlistItem = WishlistItem::factory()->create(['wishlist_id' => $this->wishlist->id]);
    }

    public function test_can_list_user_wishlists(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => UserWishlistResource::class,
        ])
            ->assertCanSeeTableRecords([$this->wishlist])
            ->assertCanSeeTableColumns([
                'user_id',
                'name',
                'description',
                'is_public',
                'is_default',
            ]);
    }

    public function test_can_create_user_wishlist(): void
    {
        $this->actingAs($this->user);

        $newWishlistData = [
            'user_id' => $this->user->id,
            'name' => 'New Wishlist',
            'description' => 'New wishlist description',
            'is_public' => true,
            'is_default' => false,
        ];

        Livewire::test(CreateRecord::class, [
            'resource' => UserWishlistResource::class,
        ])
            ->fillForm($newWishlistData)
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('user_wishlists', [
            'user_id' => $newWishlistData['user_id'],
            'name' => $newWishlistData['name'],
            'description' => $newWishlistData['description'],
            'is_public' => $newWishlistData['is_public'],
            'is_default' => $newWishlistData['is_default'],
        ]);
    }

    public function test_can_edit_user_wishlist(): void
    {
        $this->actingAs($this->user);

        $updatedData = [
            'name' => 'Updated Wishlist',
            'description' => 'Updated description',
            'is_public' => false,
            'is_default' => true,
        ];

        Livewire::test(EditRecord::class, [
            'resource' => UserWishlistResource::class,
            'record' => $this->wishlist->id,
        ])
            ->fillForm($updatedData)
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('user_wishlists', [
            'id' => $this->wishlist->id,
            'name' => $updatedData['name'],
            'description' => $updatedData['description'],
            'is_public' => $updatedData['is_public'],
            'is_default' => $updatedData['is_default'],
        ]);
    }

    public function test_can_view_user_wishlist(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ViewRecord::class, [
            'resource' => UserWishlistResource::class,
            'record' => $this->wishlist->id,
        ])
            ->assertOk();
    }

    public function test_can_filter_user_wishlists_by_user(): void
    {
        $this->actingAs($this->user);

        $anotherUser = User::factory()->create();
        $anotherWishlist = UserWishlist::factory()->create(['user_id' => $anotherUser->id]);

        Livewire::test(ListRecords::class, [
            'resource' => UserWishlistResource::class,
        ])
            ->filterTable('user_id', $this->user->id)
            ->assertCanSeeTableRecords([$this->wishlist])
            ->assertCanNotSeeTableRecords([$anotherWishlist]);
    }

    public function test_can_filter_user_wishlists_by_public_status(): void
    {
        $this->actingAs($this->user);

        $publicWishlist = UserWishlist::factory()->create([
            'user_id' => $this->user->id,
            'is_public' => true,
        ]);

        $privateWishlist = UserWishlist::factory()->create([
            'user_id' => $this->user->id,
            'is_public' => false,
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => UserWishlistResource::class,
        ])
            ->filterTable('is_public', true)
            ->assertCanSeeTableRecords([$publicWishlist])
            ->assertCanNotSeeTableRecords([$privateWishlist]);
    }

    public function test_can_filter_user_wishlists_by_default_status(): void
    {
        $this->actingAs($this->user);

        $defaultWishlist = UserWishlist::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);

        $nonDefaultWishlist = UserWishlist::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false,
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => UserWishlistResource::class,
        ])
            ->filterTable('is_default', true)
            ->assertCanSeeTableRecords([$defaultWishlist])
            ->assertCanNotSeeTableRecords([$nonDefaultWishlist]);
    }

    public function test_can_search_user_wishlists(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListRecords::class, [
            'resource' => UserWishlistResource::class,
        ])
            ->searchTable($this->wishlist->name)
            ->assertCanSeeTableRecords([$this->wishlist]);
    }

    public function test_can_sort_user_wishlists(): void
    {
        $this->actingAs($this->user);

        $anotherWishlist = UserWishlist::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->addDay(),
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => UserWishlistResource::class,
        ])
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$anotherWishlist, $this->wishlist]);
    }

    public function test_can_bulk_delete_user_wishlists(): void
    {
        $this->actingAs($this->user);

        $anotherWishlist = UserWishlist::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Livewire::test(ListRecords::class, [
            'resource' => UserWishlistResource::class,
        ])
            ->callTableBulkAction('delete', [$this->wishlist, $anotherWishlist])
            ->assertHasNoTableBulkActionErrors();

        $this->assertDatabaseMissing('user_wishlists', [
            'id' => $this->wishlist->id,
        ]);
        $this->assertDatabaseMissing('user_wishlists', [
            'id' => $anotherWishlist->id,
        ]);
    }

    public function test_form_validation_works(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateRecord::class, [
            'resource' => UserWishlistResource::class,
        ])
            ->fillForm([
                'user_id' => null,
                'name' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['user_id', 'name']);
    }

    public function test_user_wishlist_relationships_work(): void
    {
        $this->actingAs($this->user);

        $this->assertInstanceOf(User::class, $this->wishlist->user);
        $this->assertTrue($this->wishlist->items->contains($this->wishlistItem));
    }

    public function test_user_wishlist_scopes_work(): void
    {
        $this->actingAs($this->user);

        // Test scopePublic
        $publicWishlist = UserWishlist::factory()->create([
            'user_id' => $this->user->id,
            'is_public' => true,
        ]);

        $publicWishlists = UserWishlist::public()->get();
        $this->assertTrue($publicWishlists->contains($publicWishlist));
        $this->assertFalse($publicWishlists->contains($this->wishlist));

        // Test scopeDefault
        $defaultWishlist = UserWishlist::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);

        $defaultWishlists = UserWishlist::default()->get();
        $this->assertTrue($defaultWishlists->contains($defaultWishlist));
        $this->assertFalse($defaultWishlists->contains($this->wishlist));
    }

    public function test_user_wishlist_helper_methods_work(): void
    {
        $this->actingAs($this->user);

        // Test getItemsCountAttribute
        $this->assertEquals(1, $this->wishlist->items_count);

        // Test hasProduct
        $this->assertTrue($this->wishlist->hasProduct($this->wishlistItem->product_id));
        $this->assertFalse($this->wishlist->hasProduct(999));

        // Test addProduct
        $newProduct = \App\Models\Product::factory()->create();
        $addedItem = $this->wishlist->addProduct($newProduct->id, null, 2, 'Test notes');
        $this->assertInstanceOf(WishlistItem::class, $addedItem);
        $this->assertEquals($newProduct->id, $addedItem->product_id);
        $this->assertEquals(2, $addedItem->quantity);
        $this->assertEquals('Test notes', $addedItem->notes);

        // Test removeProduct
        $this->assertTrue($this->wishlist->removeProduct($this->wishlistItem->product_id));
        $this->assertFalse($this->wishlist->removeProduct(999));
    }

    public function test_user_wishlist_can_have_multiple_items(): void
    {
        $this->actingAs($this->user);

        $product1 = \App\Models\Product::factory()->create();
        $product2 = \App\Models\Product::factory()->create();

        $this->wishlist->addProduct($product1->id);
        $this->wishlist->addProduct($product2->id);

        $this->assertEquals(3, $this->wishlist->items_count);  // Original + 2 new items
        $this->assertTrue($this->wishlist->hasProduct($product1->id));
        $this->assertTrue($this->wishlist->hasProduct($product2->id));
    }

    public function test_user_wishlist_can_have_variants(): void
    {
        $this->actingAs($this->user);

        $product = \App\Models\Product::factory()->create();
        $variant = \App\Models\ProductVariant::factory()->create(['product_id' => $product->id]);

        $this->wishlist->addProduct($product->id, $variant->id, 1, 'Variant notes');

        $this->assertTrue($this->wishlist->hasProduct($product->id, $variant->id));
        $this->assertFalse($this->wishlist->hasProduct($product->id, 999));
    }
}
