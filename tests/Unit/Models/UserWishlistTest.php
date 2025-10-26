<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserWishlistTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private UserWishlist $wishlist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->wishlist = UserWishlist::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_wishlist_can_be_created(): void
    {
        $wishlist = UserWishlist::factory()->create([
            'user_id'     => $this->user->id,
            'name'        => 'Test Wishlist',
            'description' => 'Test Description',
            'is_public'   => true,
            'is_default'  => false,
        ]);

        $this->assertInstanceOf(UserWishlist::class, $wishlist);
        $this->assertEquals('Test Wishlist', $wishlist->name);
        $this->assertEquals('Test Description', $wishlist->description);
        $this->assertTrue($wishlist->is_public);
        $this->assertFalse($wishlist->is_default);
        $this->assertDatabaseHas('user_wishlists', [
            'id'      => $wishlist->id,
            'user_id' => $this->user->id,
            'name'    => 'Test Wishlist',
        ]);
    }

    public function test_user_wishlist_casts_work_correctly(): void
    {
        $wishlist = UserWishlist::factory()->create([
            'is_public'  => 1,
            'is_default' => 0,
        ]);

        $this->assertIsBool($wishlist->is_public);
        $this->assertIsBool($wishlist->is_default);
        $this->assertTrue($wishlist->is_public);
        $this->assertFalse($wishlist->is_default);
    }

    public function test_user_wishlist_belongs_to_user(): void
    {
        $this->assertInstanceOf(User::class, $this->wishlist->user);
        $this->assertEquals($this->user->id, $this->wishlist->user->id);
    }

    public function test_user_wishlist_has_many_items(): void
    {
        $item1 = WishlistItem::factory()->create(['wishlist_id' => $this->wishlist->id]);
        $item2 = WishlistItem::factory()->create(['wishlist_id' => $this->wishlist->id]);

        $this->assertCount(2, $this->wishlist->items);
        $this->assertTrue($this->wishlist->items->contains($item1));
        $this->assertTrue($this->wishlist->items->contains($item2));
    }

    public function test_get_items_count_attribute(): void
    {
        WishlistItem::factory()->count(3)->create(['wishlist_id' => $this->wishlist->id]);

        $this->assertEquals(3, $this->wishlist->items_count);
    }

    public function test_has_product_method(): void
    {
        $product = Product::factory()->create();
        WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id'  => $product->id,
        ]);

        $this->assertTrue($this->wishlist->hasProduct($product->id));
        $this->assertFalse($this->wishlist->hasProduct(99999));
    }

    public function test_has_product_with_variant(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id'  => $product->id,
            'variant_id'  => $variant->id,
        ]);

        $this->assertTrue($this->wishlist->hasProduct($product->id, $variant->id));
        $this->assertFalse($this->wishlist->hasProduct($product->id, 99999));
        $this->assertTrue($this->wishlist->hasProduct($product->id));  // Should still find product without variant
    }

    public function test_add_product_method(): void
    {
        $product = Product::factory()->create();

        $item = $this->wishlist->addProduct($product->id, null, 2, 'Test notes');

        $this->assertInstanceOf(WishlistItem::class, $item);
        $this->assertEquals($product->id, $item->product_id);
        $this->assertEquals(2, $item->quantity);
        $this->assertEquals('Test notes', $item->notes);
        $this->assertDatabaseHas('wishlist_items', [
            'wishlist_id' => $this->wishlist->id,
            'product_id'  => $product->id,
            'quantity'    => 2,
            'notes'       => 'Test notes',
        ]);
    }

    public function test_add_product_with_variant(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $item = $this->wishlist->addProduct($product->id, $variant->id, 1, 'Variant notes');

        $this->assertEquals($variant->id, $item->variant_id);
        $this->assertDatabaseHas('wishlist_items', [
            'wishlist_id' => $this->wishlist->id,
            'product_id'  => $product->id,
            'variant_id'  => $variant->id,
        ]);
    }

    public function test_remove_product_method(): void
    {
        $product = Product::factory()->create();
        $item = WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id'  => $product->id,
        ]);

        $result = $this->wishlist->removeProduct($product->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('wishlist_items', [
            'id' => $item->id,
        ]);
    }

    public function test_remove_product_with_variant(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $item = WishlistItem::factory()->create([
            'wishlist_id' => $this->wishlist->id,
            'product_id'  => $product->id,
            'variant_id'  => $variant->id,
        ]);

        $result = $this->wishlist->removeProduct($product->id, $variant->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('wishlist_items', [
            'id' => $item->id,
        ]);
    }

    public function test_remove_product_returns_false_when_product_not_found(): void
    {
        $result = $this->wishlist->removeProduct(99999);

        $this->assertFalse($result);
    }

    public function test_scope_public(): void
    {
        $publicWishlist = UserWishlist::factory()->create([
            'user_id'   => $this->user->id,
            'is_public' => true,
        ]);

        $privateWishlist = UserWishlist::factory()->create([
            'user_id'   => $this->user->id,
            'is_public' => false,
        ]);

        $publicWishlists = UserWishlist::withoutGlobalScopes()->public()->get();

        $this->assertTrue($publicWishlists->contains($publicWishlist));
        $this->assertFalse($publicWishlists->contains($privateWishlist));
    }

    public function test_scope_private(): void
    {
        $publicWishlist = UserWishlist::factory()->create([
            'user_id'   => $this->user->id,
            'is_public' => true,
        ]);

        $privateWishlist = UserWishlist::factory()->create([
            'user_id'   => $this->user->id,
            'is_public' => false,
        ]);

        $privateWishlists = UserWishlist::withoutGlobalScopes()->private()->get();

        $this->assertFalse($privateWishlists->contains($publicWishlist));
        $this->assertTrue($privateWishlists->contains($privateWishlist));
    }

    public function test_scope_default(): void
    {
        $defaultWishlist = UserWishlist::factory()->create([
            'user_id'    => $this->user->id,
            'is_default' => true,
        ]);

        $nonDefaultWishlist = UserWishlist::factory()->create([
            'user_id'    => $this->user->id,
            'is_default' => false,
        ]);

        $defaultWishlists = UserWishlist::withoutGlobalScopes()->default()->get();

        $this->assertTrue($defaultWishlists->contains($defaultWishlist));
        $this->assertFalse($defaultWishlists->contains($nonDefaultWishlist));
    }

    public function test_scope_for_user(): void
    {
        $anotherUser = User::factory()->create();
        $anotherWishlist = UserWishlist::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        $userWishlists = UserWishlist::withoutGlobalScopes()->forUser($this->user->id)->get();

        $this->assertTrue($userWishlists->contains($this->wishlist));
        $this->assertFalse($userWishlists->contains($anotherWishlist));
    }

    public function test_user_wishlist_factory_states(): void
    {
        $publicWishlist = UserWishlist::factory()->public()->create();
        $this->assertTrue($publicWishlist->is_public);

        $privateWishlist = UserWishlist::factory()->private()->create();
        $this->assertFalse($privateWishlist->is_public);

        $defaultWishlist = UserWishlist::factory()->default()->create();
        $this->assertTrue($defaultWishlist->is_default);
        $this->assertEquals('My Wishlist', $defaultWishlist->name);
    }

    public function test_user_wishlist_soft_deletes(): void
    {
        $wishlist = UserWishlist::factory()->create();
        $id = $wishlist->id;

        $wishlist->delete();

        $this->assertSoftDeleted('user_wishlists', [
            'id' => $id,
        ]);

        $this->assertNull(UserWishlist::find($id));
        $this->assertNotNull(UserWishlist::withTrashed()->find($id));
    }

    public function test_user_wishlist_cascade_deletes_items(): void
    {
        $product = Product::factory()->create();
        $item = $this->wishlist->addProduct($product->id);

        $this->wishlist->forceDelete();

        $this->assertDatabaseMissing('wishlist_items', [
            'id' => $item->id,
        ]);
    }

    public function test_timestamps_are_set(): void
    {
        $this->assertNotNull($this->wishlist->created_at);
        $this->assertNotNull($this->wishlist->updated_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $this->wishlist->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $this->wishlist->updated_at);
    }
}
