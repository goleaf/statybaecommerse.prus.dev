<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_can_be_created(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Great Product',
            'comment' => 'This product is amazing!',
        ]);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Great Product',
            'comment' => 'This product is amazing!',
        ]);
    }

    public function test_review_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $review->user);
        $this->assertEquals($user->id, $review->user->id);
    }

    public function test_review_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $review = Review::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $review->product);
        $this->assertEquals($product->id, $review->product->id);
    }

    public function test_review_casts_work_correctly(): void
    {
        $review = Review::factory()->create([
            'rating' => 4,
            'is_approved' => true,
            'created_at' => now(),
        ]);

        $this->assertIsInt($review->rating);
        $this->assertIsBool($review->is_approved);
        $this->assertInstanceOf(\Carbon\Carbon::class, $review->created_at);
    }

    public function test_review_fillable_attributes(): void
    {
        $review = new Review();
        $fillable = $review->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('product_id', $fillable);
        $this->assertContains('rating', $fillable);
        $this->assertContains('title', $fillable);
        $this->assertContains('comment', $fillable);
    }

    public function test_review_scope_approved(): void
    {
        $approvedReview = Review::factory()->create(['is_approved' => true]);
        $pendingReview = Review::factory()->create(['is_approved' => false]);

        $approvedReviews = Review::approved()->get();

        $this->assertTrue($approvedReviews->contains($approvedReview));
        $this->assertFalse($approvedReviews->contains($pendingReview));
    }

    public function test_review_scope_pending(): void
    {
        $approvedReview = Review::factory()->create(['is_approved' => true]);
        $pendingReview = Review::factory()->create(['is_approved' => false]);

        $pendingReviews = Review::pending()->get();

        $this->assertFalse($pendingReviews->contains($approvedReview));
        $this->assertTrue($pendingReviews->contains($pendingReview));
    }

    public function test_review_scope_by_rating(): void
    {
        $fiveStarReview = Review::factory()->create(['rating' => 5]);
        $fourStarReview = Review::factory()->create(['rating' => 4]);
        $threeStarReview = Review::factory()->create(['rating' => 3]);

        $highRatingReviews = Review::byRating(4)->get();

        $this->assertTrue($highRatingReviews->contains($fiveStarReview));
        $this->assertTrue($highRatingReviews->contains($fourStarReview));
        $this->assertFalse($highRatingReviews->contains($threeStarReview));
    }

    public function test_review_scope_for_product(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        $review1 = Review::factory()->create(['product_id' => $product1->id]);
        $review2 = Review::factory()->create(['product_id' => $product2->id]);

        $product1Reviews = Review::forProduct($product1->id)->get();

        $this->assertTrue($product1Reviews->contains($review1));
        $this->assertFalse($product1Reviews->contains($review2));
    }

    public function test_review_scope_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $review1 = Review::factory()->create(['user_id' => $user1->id]);
        $review2 = Review::factory()->create(['user_id' => $user2->id]);

        $user1Reviews = Review::byUser($user1->id)->get();

        $this->assertTrue($user1Reviews->contains($review1));
        $this->assertFalse($user1Reviews->contains($review2));
    }

    public function test_review_can_have_helpful_votes(): void
    {
        $review = Review::factory()->create([
            'helpful_votes' => 10,
            'total_votes' => 15,
        ]);

        $this->assertEquals(10, $review->helpful_votes);
        $this->assertEquals(15, $review->total_votes);
    }

    public function test_review_can_have_verified_purchase(): void
    {
        $review = Review::factory()->create([
            'verified_purchase' => true,
        ]);

        $this->assertTrue($review->verified_purchase);
    }

    public function test_review_can_have_admin_response(): void
    {
        $review = Review::factory()->create([
            'admin_response' => 'Thank you for your feedback!',
            'admin_response_date' => now(),
        ]);

        $this->assertEquals('Thank you for your feedback!', $review->admin_response);
        $this->assertInstanceOf(\Carbon\Carbon::class, $review->admin_response_date);
    }

    public function test_review_can_have_media(): void
    {
        $review = Review::factory()->create();

        // Test that review implements HasMedia
        $this->assertInstanceOf(\Spatie\MediaLibrary\HasMedia::class, $review);
        
        // Test that review can handle media
        $this->assertTrue(method_exists($review, 'registerMediaCollections'));
        $this->assertTrue(method_exists($review, 'registerMediaConversions'));
        $this->assertTrue(method_exists($review, 'media'));
    }

    public function test_review_rating_validation(): void
    {
        $review = Review::factory()->create(['rating' => 5]);
        
        $this->assertGreaterThanOrEqual(1, $review->rating);
        $this->assertLessThanOrEqual(5, $review->rating);
    }

    public function test_review_can_have_anonymous_author(): void
    {
        $review = Review::factory()->create([
            'user_id' => null,
            'author_name' => 'Anonymous User',
            'author_email' => 'anonymous@example.com',
        ]);

        $this->assertNull($review->user_id);
        $this->assertEquals('Anonymous User', $review->author_name);
        $this->assertEquals('anonymous@example.com', $review->author_email);
    }
}
