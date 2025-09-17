<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CanBeOneOfManyFinalUltimateTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_lowest_value_item_relationship(): void
    {
        $order = Order::factory()->create();
        
        // Create multiple order items with different totals
        $highValueItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'total' => 100.00,
            'name' => 'High Value Item',
            'sku' => 'HIGH-001',
        ]);
        
        $lowValueItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'total' => 10.00,
            'name' => 'Low Value Item',
            'sku' => 'LOW-001',
        ]);

        // Refresh the order to clear any cached relationships
        $order->refresh();

        // Test the lowestValueItem relationship
        $this->assertInstanceOf(OrderItem::class, $order->lowestValueItem);
        $this->assertEquals($lowValueItem->id, $order->lowestValueItem->id);
        $this->assertEquals(10.00, $order->lowestValueItem->total);
        $this->assertNotEquals($highValueItem->id, $order->lowestValueItem->id);
    }

    public function test_product_lowest_rated_review_relationship(): void
    {
        $product = Product::factory()->create();
        
        // Create multiple reviews with different ratings
        $highRatedReview = Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 5,
        ]);
        
        $lowRatedReview = Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 1,
        ]);

        // Refresh the product to clear any cached relationships
        $product->refresh();

        // Test the lowestRatedReview relationship
        $this->assertInstanceOf(Review::class, $product->lowestRatedReview);
        $this->assertEquals($lowRatedReview->id, $product->lowestRatedReview->id);
        $this->assertEquals(1, $product->lowestRatedReview->rating);
        $this->assertNotEquals($highRatedReview->id, $product->lowestRatedReview->id);
    }

    public function test_user_lowest_rated_review_relationship(): void
    {
        $user = User::factory()->create();
        
        // Create multiple reviews with different ratings
        $highRatedReview = Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 5,
        ]);
        
        $lowRatedReview = Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 1,
        ]);

        // Refresh the user to clear any cached relationships
        $user->refresh();

        // Test the lowestRatedReview relationship
        $this->assertInstanceOf(Review::class, $user->lowestRatedReview);
        $this->assertEquals($lowRatedReview->id, $user->lowestRatedReview->id);
        $this->assertEquals(1, $user->lowestRatedReview->rating);
        $this->assertNotEquals($highRatedReview->id, $user->lowestRatedReview->id);
    }
}
