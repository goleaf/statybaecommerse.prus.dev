<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Events\ReviewSubmittedForModeration;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * @internal
 */
final class ReviewSubmissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure an authenticated customer can submit a review and receives the resource payload back.
     */
    public function test_authenticated_user_can_submit_review(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->completed()->create([
            'user_id' => $user->id,
        ]);
        OrderItem::factory()->forOrder($order)->forProduct($product)->create();

        $payload = [
            'rating' => 5,
            'title' => 'Excellent quality',
            'content' => str_repeat('Great product! ', 3),
            'order_id' => $order->id,
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.products.reviews.store', $product), $payload);

        $response->assertCreated();
        $response->assertJsonPath('data.rating', 5);
        $response->assertJsonPath('data.is_verified_purchase', true);
        $response->assertJsonPath('data.metadata.order_id', $order->id);

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
            'metadata->order_id' => $order->id,
        ]);

        Event::assertDispatched(ReviewSubmittedForModeration::class);
    }

    /**
     * Ensure the API surfaces validation errors with the expected 422 status code.
     */
    public function test_validation_errors_are_returned(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.products.reviews.store', $product), [
                'rating' => 7,
                'content' => 'Too short',
                'order_id' => 0,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating', 'content', 'order_id']);
    }

    /**
     * Ensure guests cannot submit reviews and receive an authentication error.
     */
    public function test_guest_cannot_submit_review(): void
    {
        $product = Product::factory()->create();

        $this->postJson(route('api.products.reviews.store', $product), [])->assertStatus(401);
    }

    /**
     * Ensure duplicate submissions for the same order and product combination are rejected.
     */
    public function test_duplicate_review_returns_conflict(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->completed()->create([
            'user_id' => $user->id,
        ]);
        OrderItem::factory()->forOrder($order)->forProduct($product)->create();

        Review::query()->withoutGlobalScopes()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'reviewer_name' => $user->name ?? 'Tester',
            'reviewer_email' => $user->email,
            'rating' => 4,
            'content' => 'Solid product',
            'is_approved' => false,
            'is_featured' => false,
            'is_verified_purchase' => true,
            'locale' => 'en',
            'metadata' => ['order_id' => $order->id],
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.products.reviews.store', $product), [
                'rating' => 4,
                'content' => str_repeat('Solid product ', 2),
                'order_id' => $order->id,
            ]);

        $response->assertStatus(409);
    }
}
