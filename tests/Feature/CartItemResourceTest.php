<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CartItemResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->create());
    }

    public function test_can_create_cart_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $cartItemData = [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'is_active' => true,
            'is_saved_for_later' => false,
        ];

        $cartItem = CartItem::create($cartItemData);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'is_active' => true,
            'is_saved_for_later' => false,
        ]);

        $this->assertEquals($user->id, $cartItem->user_id);
        $this->assertEquals($product->id, $cartItem->product_id);
        $this->assertEquals(2, $cartItem->quantity);
        $this->assertTrue($cartItem->is_active);
        $this->assertFalse($cartItem->is_saved_for_later);
    }

    public function test_can_update_cart_item(): void
    {
        $cartItem = CartItem::factory()->create();

        $cartItem->update([
            'quantity' => 5,
            'is_saved_for_later' => true,
        ]);

        $this->assertEquals(5, $cartItem->quantity);
        $this->assertTrue($cartItem->is_saved_for_later);
    }

    public function test_can_filter_cart_items_by_active_status(): void
    {
        CartItem::factory()->create(['is_active' => true]);
        CartItem::factory()->create(['is_active' => false]);

        $activeCartItems = CartItem::where('is_active', true)->get();
        $inactiveCartItems = CartItem::where('is_active', false)->get();

        $this->assertCount(1, $activeCartItems);
        $this->assertCount(1, $inactiveCartItems);
        $this->assertTrue($activeCartItems->first()->is_active);
        $this->assertFalse($inactiveCartItems->first()->is_active);
    }

    public function test_can_filter_cart_items_by_saved_for_later(): void
    {
        CartItem::factory()->create(['is_saved_for_later' => true]);
        CartItem::factory()->create(['is_saved_for_later' => false]);

        $savedCartItems = CartItem::where('is_saved_for_later', true)->get();
        $regularCartItems = CartItem::where('is_saved_for_later', false)->get();

        $this->assertCount(1, $savedCartItems);
        $this->assertCount(1, $regularCartItems);
        $this->assertTrue($savedCartItems->first()->is_saved_for_later);
        $this->assertFalse($regularCartItems->first()->is_saved_for_later);
    }

    public function test_can_get_cart_item_with_user_relationship(): void
    {
        $user = User::factory()->create();
        $cartItem = CartItem::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $cartItem->user);
        $this->assertEquals($user->id, $cartItem->user->id);
    }

    public function test_can_get_cart_item_with_product_relationship(): void
    {
        $product = Product::factory()->create();
        $cartItem = CartItem::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $cartItem->product);
        $this->assertEquals($product->id, $cartItem->product->id);
    }

    public function test_can_calculate_total_cart_value(): void
    {
        $user = User::factory()->create();
        
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);
        
        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);

        $totalItems = CartItem::where('user_id', $user->id)->sum('quantity');

        $this->assertEquals(5, $totalItems);
    }

    public function test_can_soft_delete_cart_item(): void
    {
        $cartItem = CartItem::factory()->create();

        $cartItem->delete();

        $this->assertSoftDeleted('cart_items', [
            'id' => $cartItem->id,
        ]);
    }
}