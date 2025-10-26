<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Http\Controllers\ReviewController;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ReviewSubmissionTest
 *
 * Feature tests covering the storefront review submission and ownership flows.
 */
final class ReviewSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_submit_review(): void
    {
        // Arrange: create a product so the payload targets a valid catalogue item.
        $product = Product::factory()->create();

        // Act: attempt to submit a review as a guest user.
        $response = $this->post(route('frontend.reviews.store', $product), [
            'product_id'     => $product->id,
            'rating'         => 5,
            'title'          => 'Excellent product',
            'content'        => 'Truly outstanding craftsmanship!',
            'reviewer_name'  => 'Guest Author',
            'reviewer_email' => 'guest@example.com',
        ]);

        // Assert: ensure the request is rejected and nothing is persisted.
        $response->assertUnauthorized();
        self::assertDatabaseCount(Review::class, 0);
    }

    public function test_authenticated_user_can_submit_review_and_become_owner(): void
    {
        // Arrange: seed an authenticated customer and a product ready to review.
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->actingAs($user);

        // Act: submit a review payload as the logged-in user.
        $response = $this->post(route('frontend.reviews.store', $product), [
            'product_id'     => $product->id,
            'rating'         => 4,
            'title'          => 'Solid quality',
            'content'        => 'The build quality is impressive and worth the price.',
            'reviewer_name'  => 'Jane Doe',
            'reviewer_email' => 'jane@example.com',
        ]);

        // Assert: confirm persistence assigns ownership and enforces pending moderation.
        $review = Review::withoutGlobalScopes()->first();
        self::assertNotNull($review);
        self::assertSame($user->id, $review->user_id);
        self::assertFalse((bool) $review->is_approved);
        $response->assertRedirect(route('reviews.show', $review->getKey()));
    }

    public function test_owner_can_view_pending_review_after_update(): void
    {
        // Arrange: produce an approved review tied to a customer and product.
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $review = Review::factory()
            ->for($product)
            ->for($user)
            ->create([
                'is_approved' => true,
            ]);
        $this->actingAs($user);

        // Act: update the review which should flip moderation back to pending.
        $response = $this->put(route('frontend.reviews.update', $review), [
            'rating'  => 3,
            'title'   => 'Updated perspective',
            'content' => 'After more usage the experience remains positive overall.',
        ]);

        // Assert: verify the update response, moderation status, and owner visibility.
        $response->assertRedirect(route('reviews.show', $review->getKey()));
        $review->refresh();
        self::assertFalse((bool) $review->is_approved);
        $reviewKey = $review->getKey();
        self::assertNotNull($reviewKey);
        /** @var int|string $reviewKey */
        $reviewKey = $reviewKey;
        $view = app(ReviewController::class)->show((string) $reviewKey);
        self::assertSame('reviews.show', $view->name());
        $resolvedReview = $view->getData()['review'];
        self::assertInstanceOf(Review::class, $resolvedReview);
        self::assertTrue($resolvedReview->is($review));
    }
}
