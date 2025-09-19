<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\WishlistItem;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_item_can_be_created(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $wishlist = \App\Models\UserWishlist::factory()->create(['user_id' => $user->id]);
        
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'product_id' => $product->id,
        ]);

        $this->assertDatabaseHas('wishlist_items', [
            'wishlist_id' => $wishlist->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_wishlist_item_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $wishlist = \App\Models\UserWishlist::factory()->create(['user_id' => $user->id]);
        $wishlistItem = WishlistItem::factory()->create(['wishlist_id' => $wishlist->id]);

        $this->assertInstanceOf(\App\Models\UserWishlist::class, $wishlistItem->wishlist);
        $this->assertEquals($wishlist->id, $wishlistItem->wishlist->id);
        $this->assertEquals($user->id, $wishlistItem->wishlist->user_id);
    }

    public function test_wishlist_item_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $wishlist = \App\Models\UserWishlist::factory()->create();
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'product_id' => $product->id
        ]);

        $this->assertInstanceOf(Product::class, $wishlistItem->product);
        $this->assertEquals($product->id, $wishlistItem->product->id);
    }

    public function test_wishlist_item_can_have_product_variant(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $wishlist = \App\Models\UserWishlist::factory()->create();
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
        ]);

        $this->assertInstanceOf(ProductVariant::class, $wishlistItem->variant);
        $this->assertEquals($variant->id, $wishlistItem->variant->id);
    }

    public function test_wishlist_item_casts_work_correctly(): void
    {
        $wishlist = \App\Models\UserWishlist::factory()->create();
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'created_at' => now(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $wishlistItem->created_at);
    }

    public function test_wishlist_item_fillable_attributes(): void
    {
        $wishlistItem = new WishlistItem();
        $fillable = $wishlistItem->getFillable();

        $this->assertContains('wishlist_id', $fillable);
        $this->assertContains('product_id', $fillable);
        $this->assertContains('variant_id', $fillable);
    }

    public function test_wishlist_item_scope_for_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $wishlist1 = \App\Models\UserWishlist::factory()->create(['user_id' => $user1->id]);
        $wishlist2 = \App\Models\UserWishlist::factory()->create(['user_id' => $user2->id]);
        
        $wishlistItem1 = WishlistItem::factory()->create(['wishlist_id' => $wishlist1->id]);
        $wishlistItem2 = WishlistItem::factory()->create(['wishlist_id' => $wishlist2->id]);

        $user1WishlistItems = WishlistItem::forUser($user1->id)->get();

        $this->assertTrue($user1WishlistItems->contains($wishlistItem1));
        $this->assertFalse($user1WishlistItems->contains($wishlistItem2));
    }

    public function test_wishlist_item_scope_for_product(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        $wishlist = \App\Models\UserWishlist::factory()->create();
        $wishlistItem1 = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'product_id' => $product1->id
        ]);
        $wishlistItem2 = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'product_id' => $product2->id
        ]);

        $product1WishlistItems = WishlistItem::forProduct($product1->id)->get();

        $this->assertTrue($product1WishlistItems->contains($wishlistItem1));
        $this->assertFalse($product1WishlistItems->contains($wishlistItem2));
    }

    public function test_wishlist_item_scope_recent(): void
    {
        $wishlist = \App\Models\UserWishlist::factory()->create();
        $recentWishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'created_at' => now()
        ]);
        $oldWishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'created_at' => now()->subDays(10)
        ]);

        $recentWishlistItems = WishlistItem::recent()->get();

        $this->assertTrue($recentWishlistItems->contains($recentWishlistItem));
        $this->assertFalse($recentWishlistItems->contains($oldWishlistItem));
    }

    public function test_wishlist_item_can_have_notes(): void
    {
        $wishlist = \App\Models\UserWishlist::factory()->create();
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'notes' => 'Want this for birthday gift',
        ]);

        $this->assertEquals('Want this for birthday gift', $wishlistItem->notes);
    }

    public function test_wishlist_item_can_have_quantity(): void
    {
        $wishlist = \App\Models\UserWishlist::factory()->create();
        $wishlistItem = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'quantity' => 2,
        ]);

        $this->assertEquals(2, $wishlistItem->quantity);
    }
}
