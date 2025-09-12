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
        
        $wishlistItem = WishlistItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_wishlist_item_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $wishlistItem = WishlistItem::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $wishlistItem->user);
        $this->assertEquals($user->id, $wishlistItem->user->id);
    }

    public function test_wishlist_item_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $wishlistItem = WishlistItem::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $wishlistItem->product);
        $this->assertEquals($product->id, $wishlistItem->product->id);
    }

    public function test_wishlist_item_can_have_product_variant(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $wishlistItem = WishlistItem::factory()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
        ]);

        $this->assertInstanceOf(ProductVariant::class, $wishlistItem->productVariant);
        $this->assertEquals($variant->id, $wishlistItem->productVariant->id);
    }

    public function test_wishlist_item_casts_work_correctly(): void
    {
        $wishlistItem = WishlistItem::factory()->create([
            'created_at' => now(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $wishlistItem->created_at);
    }

    public function test_wishlist_item_fillable_attributes(): void
    {
        $wishlistItem = new WishlistItem();
        $fillable = $wishlistItem->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('product_id', $fillable);
        $this->assertContains('product_variant_id', $fillable);
    }

    public function test_wishlist_item_scope_for_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $wishlistItem1 = WishlistItem::factory()->create(['user_id' => $user1->id]);
        $wishlistItem2 = WishlistItem::factory()->create(['user_id' => $user2->id]);

        $user1WishlistItems = WishlistItem::forUser($user1->id)->get();

        $this->assertTrue($user1WishlistItems->contains($wishlistItem1));
        $this->assertFalse($user1WishlistItems->contains($wishlistItem2));
    }

    public function test_wishlist_item_scope_for_product(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        $wishlistItem1 = WishlistItem::factory()->create(['product_id' => $product1->id]);
        $wishlistItem2 = WishlistItem::factory()->create(['product_id' => $product2->id]);

        $product1WishlistItems = WishlistItem::forProduct($product1->id)->get();

        $this->assertTrue($product1WishlistItems->contains($wishlistItem1));
        $this->assertFalse($product1WishlistItems->contains($wishlistItem2));
    }

    public function test_wishlist_item_scope_recent(): void
    {
        $recentWishlistItem = WishlistItem::factory()->create(['created_at' => now()]);
        $oldWishlistItem = WishlistItem::factory()->create(['created_at' => now()->subDays(10)]);

        $recentWishlistItems = WishlistItem::recent()->get();

        $this->assertTrue($recentWishlistItems->contains($recentWishlistItem));
        $this->assertFalse($recentWishlistItems->contains($oldWishlistItem));
    }

    public function test_wishlist_item_can_have_notes(): void
    {
        $wishlistItem = WishlistItem::factory()->create([
            'notes' => 'Want this for birthday gift',
        ]);

        $this->assertEquals('Want this for birthday gift', $wishlistItem->notes);
    }

    public function test_wishlist_item_can_have_priority(): void
    {
        $wishlistItem = WishlistItem::factory()->create([
            'priority' => 'high',
        ]);

        $this->assertEquals('high', $wishlistItem->priority);
    }

    public function test_wishlist_item_can_have_reminder_date(): void
    {
        $wishlistItem = WishlistItem::factory()->create([
            'reminder_date' => now()->addDays(30),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $wishlistItem->reminder_date);
    }

    public function test_wishlist_item_can_have_quantity(): void
    {
        $wishlistItem = WishlistItem::factory()->create([
            'quantity' => 2,
        ]);

        $this->assertEquals(2, $wishlistItem->quantity);
    }

    public function test_wishlist_item_can_have_price_when_added(): void
    {
        $wishlistItem = WishlistItem::factory()->create([
            'price_when_added' => 99.99,
        ]);

        $this->assertEquals(99.99, $wishlistItem->price_when_added);
    }

    public function test_wishlist_item_can_have_custom_attributes(): void
    {
        $wishlistItem = WishlistItem::factory()->create([
            'attributes' => [
                'color' => 'red',
                'size' => 'large',
                'gift_wrap' => true,
            ],
        ]);

        $this->assertIsArray($wishlistItem->attributes);
        $this->assertEquals('red', $wishlistItem->attributes['color']);
        $this->assertEquals('large', $wishlistItem->attributes['size']);
        $this->assertTrue($wishlistItem->attributes['gift_wrap']);
    }

    public function test_wishlist_item_can_have_tags(): void
    {
        $wishlistItem = WishlistItem::factory()->create([
            'tags' => ['gift', 'birthday', 'electronics'],
        ]);

        $this->assertIsArray($wishlistItem->tags);
        $this->assertContains('gift', $wishlistItem->tags);
        $this->assertContains('birthday', $wishlistItem->tags);
        $this->assertContains('electronics', $wishlistItem->tags);
    }

    public function test_wishlist_item_can_have_metadata(): void
    {
        $wishlistItem = WishlistItem::factory()->create([
            'metadata' => [
                'source' => 'product_page',
                'campaign' => 'summer_sale',
                'referrer' => 'google',
            ],
        ]);

        $this->assertIsArray($wishlistItem->metadata);
        $this->assertEquals('product_page', $wishlistItem->metadata['source']);
        $this->assertEquals('summer_sale', $wishlistItem->metadata['campaign']);
        $this->assertEquals('google', $wishlistItem->metadata['referrer']);
    }

    public function test_wishlist_item_can_check_if_reminder_due(): void
    {
        $reminderDueItem = WishlistItem::factory()->create([
            'reminder_date' => now()->subDay(),
        ]);

        $reminderNotDueItem = WishlistItem::factory()->create([
            'reminder_date' => now()->addDay(),
        ]);

        $this->assertTrue($reminderDueItem->isReminderDue());
        $this->assertFalse($reminderNotDueItem->isReminderDue());
    }

    public function test_wishlist_item_can_check_price_change(): void
    {
        $wishlistItem = WishlistItem::factory()->create([
            'price_when_added' => 100.00,
        ]);

        $product = $wishlistItem->product;
        $product->price = 80.00;
        $product->save();

        $this->assertTrue($wishlistItem->hasPriceChanged());
        $this->assertEquals(20.00, $wishlistItem->getPriceDifference());
    }

    public function test_wishlist_item_can_have_shared_status(): void
    {
        $wishlistItem = WishlistItem::factory()->create([
            'is_shared' => true,
            'shared_with' => ['user1@example.com', 'user2@example.com'],
        ]);

        $this->assertTrue($wishlistItem->is_shared);
        $this->assertIsArray($wishlistItem->shared_with);
        $this->assertContains('user1@example.com', $wishlistItem->shared_with);
        $this->assertContains('user2@example.com', $wishlistItem->shared_with);
    }

    public function test_wishlist_item_can_have_visibility(): void
    {
        $wishlistItem = WishlistItem::factory()->create([
            'is_public' => true,
        ]);

        $this->assertTrue($wishlistItem->is_public);
    }
}
