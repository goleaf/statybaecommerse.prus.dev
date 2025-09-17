<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CartItem;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_item_can_be_created(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $cartItem = CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 99.99,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 99.99,
        ]);
    }

    public function test_cart_item_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $cartItem = CartItem::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $cartItem->user);
        $this->assertEquals($user->id, $cartItem->user->id);
    }

    public function test_cart_item_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $cartItem = CartItem::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $cartItem->product);
        $this->assertEquals($product->id, $cartItem->product->id);
    }

    public function test_cart_item_can_have_product_variant(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $cartItem = CartItem::factory()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
        ]);

        $this->assertInstanceOf(ProductVariant::class, $cartItem->productVariant);
        $this->assertEquals($variant->id, $cartItem->productVariant->id);
    }

    public function test_cart_item_casts_work_correctly(): void
    {
        $cartItem = CartItem::factory()->create([
            'quantity' => 5,
            'price' => 49.99,
            'created_at' => now(),
        ]);

        $this->assertIsInt($cartItem->quantity);
        $this->assertIsNumeric($cartItem->price);
        $this->assertInstanceOf(\Carbon\Carbon::class, $cartItem->created_at);
    }

    public function test_cart_item_fillable_attributes(): void
    {
        $cartItem = new CartItem();
        $fillable = $cartItem->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('product_id', $fillable);
        $this->assertContains('product_variant_id', $fillable);
        $this->assertContains('quantity', $fillable);
        $this->assertContains('price', $fillable);
    }

    public function test_cart_item_can_calculate_subtotal(): void
    {
        $cartItem = CartItem::factory()->create([
            'quantity' => 3,
            'price' => 25.00,
        ]);

        $expectedSubtotal = 3 * 25.00;
        $this->assertEquals($expectedSubtotal, $cartItem->subtotal);
    }

    public function test_cart_item_scope_for_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $cartItem1 = CartItem::factory()->create(['user_id' => $user1->id]);
        $cartItem2 = CartItem::factory()->create(['user_id' => $user2->id]);

        $user1CartItems = CartItem::forUser($user1->id)->get();

        $this->assertTrue($user1CartItems->contains($cartItem1));
        $this->assertFalse($user1CartItems->contains($cartItem2));
    }

    public function test_cart_item_scope_for_product(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        $cartItem1 = CartItem::factory()->create(['product_id' => $product1->id]);
        $cartItem2 = CartItem::factory()->create(['product_id' => $product2->id]);

        $product1CartItems = CartItem::forProduct($product1->id)->get();

        $this->assertTrue($product1CartItems->contains($cartItem1));
        $this->assertFalse($product1CartItems->contains($cartItem2));
    }

    public function test_cart_item_can_update_quantity(): void
    {
        $cartItem = CartItem::factory()->create(['quantity' => 1]);
        
        $cartItem->updateQuantity(5);
        
        $this->assertEquals(5, $cartItem->quantity);
    }

    public function test_cart_item_can_increment_quantity(): void
    {
        $cartItem = CartItem::factory()->create(['quantity' => 2]);
        
        $cartItem->incrementQuantity(3);
        
        $this->assertEquals(5, $cartItem->quantity);
    }

    public function test_cart_item_can_decrement_quantity(): void
    {
        $cartItem = CartItem::factory()->create(['quantity' => 5]);
        
        $cartItem->decrementQuantity(2);
        
        $this->assertEquals(3, $cartItem->quantity);
    }

    public function test_cart_item_can_remove_when_quantity_zero(): void
    {
        $cartItem = CartItem::factory()->create(['quantity' => 1]);
        
        $cartItem->decrementQuantity(1);
        
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_cart_item_has_session_id(): void
    {
        $cartItem = CartItem::factory()->create([
            'session_id' => 'test-session-123',
        ]);

        $this->assertEquals('test-session-123', $cartItem->session_id);
    }

    public function test_cart_item_can_have_notes(): void
    {
        $cartItem = CartItem::factory()->create([
            'notes' => 'Special instructions for this item',
        ]);

        $this->assertEquals('Special instructions for this item', $cartItem->notes);
    }

    public function test_cart_item_can_have_custom_attributes(): void
    {
        $cartItem = CartItem::factory()->create([
            'attributes' => [
                'color' => 'red',
                'size' => 'large',
            ],
        ]);

        $this->assertIsArray($cartItem->attributes);
        $this->assertEquals('red', $cartItem->attributes['color']);
        $this->assertEquals('large', $cartItem->attributes['size']);
    }
}