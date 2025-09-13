<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function createTestProduct(): Product
    {
        return Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST001',
        ]);
    }

    private function createTestUser(): User
    {
        return User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_review_can_be_created(): void
    {
        $product = $this->createTestProduct();
        $user = $this->createTestUser();
        
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'reviewer_name' => 'John Doe',
            'reviewer_email' => 'john@example.com',
            'rating' => 5,
            'title' => 'Great Product',
            'comment' => 'This product is amazing!',
            'is_approved' => false,
            'is_featured' => false,
            'locale' => 'en',
        ]);

        $this->assertInstanceOf(Review::class, $review);
        $this->assertEquals($product->id, $review->product_id);
        $this->assertEquals($user->id, $review->user_id);
        $this->assertEquals('John Doe', $review->reviewer_name);
        $this->assertEquals('john@example.com', $review->reviewer_email);
        $this->assertEquals(5, $review->rating);
        $this->assertEquals('Great Product', $review->title);
        $this->assertEquals('This product is amazing!', $review->comment);
        $this->assertFalse($review->is_approved);
        $this->assertFalse($review->is_featured);
        $this->assertEquals('en', $review->locale);
    }

    public function test_review_translation_methods(): void
    {
        $product = $this->createTestProduct();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'title' => 'Original Title',
            'comment' => 'Original Comment',
        ]);
        
        // Test translation methods
        $this->assertEquals('Original Title', $review->getTranslatedTitle());
        $this->assertEquals('Original Comment', $review->getTranslatedComment());
        
        // Test with translation
        $review->updateTranslation('en', [
            'title' => 'English Title',
            'comment' => 'English Comment',
        ]);
        
        $this->assertEquals('English Title', $review->getTranslatedTitle('en'));
        $this->assertEquals('English Comment', $review->getTranslatedComment('en'));
    }

    public function test_review_scopes(): void
    {
        $product = $this->createTestProduct();
        
        // Clear any existing reviews first
        Review::query()->delete();

        // Create test reviews with specific attributes
        $approvedReview = Review::factory()->create([
            'product_id' => $product->id,
            'is_approved' => true,
            'rating' => 5,
        ]);
        $pendingReview = Review::factory()->create([
            'product_id' => $product->id,
            'is_approved' => false,
            'rejected_at' => null,
        ]);
        $featuredReview = Review::factory()->create([
            'product_id' => $product->id,
            'is_featured' => true,
        ]);
        $highRatedReview = Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 5,
        ]);

        // Test approved scope
        $approvedReviews = Review::approved()->get();
        $this->assertCount(1, $approvedReviews);
        $this->assertEquals($approvedReview->id, $approvedReviews->first()->id);

        // Test pending scope
        $pendingReviews = Review::pending()->get();
        $this->assertCount(1, $pendingReviews);
        $this->assertEquals($pendingReview->id, $pendingReviews->first()->id);

        // Test featured scope
        $featuredReviews = Review::featured()->get();
        $this->assertCount(1, $featuredReviews);
        $this->assertEquals($featuredReview->id, $featuredReviews->first()->id);

        // Test high rated scope
        $highRatedReviews = Review::highRated(4)->get();
        $this->assertCount(1, $highRatedReviews);
        $this->assertEquals($highRatedReview->id, $highRatedReviews->first()->id);
    }

    public function test_review_helper_methods(): void
    {
        $product = $this->createTestProduct();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 4,
            'is_approved' => true,
            'is_featured' => false,
            'title' => 'Great Product',
        ]);

        // Test info methods
        $reviewInfo = $review->getReviewInfo();
        $this->assertArrayHasKey('id', $reviewInfo);
        $this->assertArrayHasKey('product_id', $reviewInfo);
        $this->assertArrayHasKey('rating', $reviewInfo);

        $statusInfo = $review->getStatusInfo();
        $this->assertArrayHasKey('is_approved', $statusInfo);
        $this->assertArrayHasKey('status', $statusInfo);
        $this->assertArrayHasKey('status_color', $statusInfo);

        $ratingInfo = $review->getRatingInfo();
        $this->assertArrayHasKey('rating', $ratingInfo);
        $this->assertArrayHasKey('rating_stars', $ratingInfo);
        $this->assertArrayHasKey('is_high_rated', $ratingInfo);

        $businessInfo = $review->getBusinessInfo();
        $this->assertArrayHasKey('is_approved', $businessInfo);
        $this->assertArrayHasKey('is_recent', $businessInfo);
        $this->assertArrayHasKey('reviewer_type', $businessInfo);

        $completeInfo = $review->getCompleteInfo();
        $this->assertArrayHasKey('translations', $completeInfo);
        $this->assertArrayHasKey('has_translations', $completeInfo);
    }

    public function test_review_status_methods(): void
    {
        $product = $this->createTestProduct();
        
        // Test approved review
        $approvedReview = Review::factory()->create([
            'product_id' => $product->id,
            'is_approved' => true,
        ]);
        $this->assertEquals('approved', $approvedReview->getStatus());
        $this->assertEquals('success', $approvedReview->getStatusColor());
        $this->assertTrue($approvedReview->canBeRejected());
        $this->assertFalse($approvedReview->canBeApproved());

        // Test rejected review
        $rejectedReview = Review::factory()->create([
            'product_id' => $product->id,
            'is_approved' => false,
            'rejected_at' => now(),
        ]);
        $this->assertEquals('rejected', $rejectedReview->getStatus());
        $this->assertEquals('danger', $rejectedReview->getStatusColor());
        $this->assertFalse($rejectedReview->canBeApproved());
        $this->assertFalse($rejectedReview->canBeRejected());

        // Test pending review
        $pendingReview = Review::factory()->create([
            'product_id' => $product->id,
            'is_approved' => false,
            'rejected_at' => null,
        ]);
        $this->assertEquals('pending', $pendingReview->getStatus());
        $this->assertEquals('warning', $pendingReview->getStatusColor());
        $this->assertTrue($pendingReview->canBeApproved());
        $this->assertTrue($pendingReview->canBeRejected());
    }

    public function test_review_rating_methods(): void
    {
        $product = $this->createTestProduct();
        
        // Test 5-star review
        $excellentReview = Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 5,
        ]);
        $this->assertTrue($excellentReview->isHighRated());
        $this->assertFalse($excellentReview->isLowRated());
        $this->assertEquals('success', $excellentReview->getRatingColor());

        // Test 1-star review
        $poorReview = Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 1,
        ]);
        $this->assertFalse($poorReview->isHighRated());
        $this->assertTrue($poorReview->isLowRated());
        $this->assertEquals('danger', $poorReview->getRatingColor());

        // Test 3-star review
        $averageReview = Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 3,
        ]);
        $this->assertFalse($averageReview->isHighRated());
        $this->assertFalse($averageReview->isLowRated());
        $this->assertEquals('warning', $averageReview->getRatingColor());
    }

    public function test_review_approval_methods(): void
    {
        $product = $this->createTestProduct();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'is_approved' => false,
            'approved_at' => null,
            'rejected_at' => null,
        ]);

        // Test approval
        $this->assertTrue($review->approve());
        $this->assertTrue($review->is_approved);
        $this->assertNotNull($review->approved_at);
        $this->assertNull($review->rejected_at);

        // Test rejection
        $review->update(['is_approved' => false, 'approved_at' => null]);
        $this->assertTrue($review->reject());
        $this->assertFalse($review->is_approved);
        $this->assertNotNull($review->rejected_at);
        $this->assertNull($review->approved_at);
    }

    public function test_review_feature_methods(): void
    {
        $product = $this->createTestProduct();
        
        // Test can be featured
        $approvedReview = Review::factory()->create([
            'product_id' => $product->id,
            'is_approved' => true,
            'is_featured' => false,
        ]);
        $this->assertTrue($approvedReview->canBeFeatured());
        $this->assertFalse($approvedReview->canBeUnfeatured());

        // Test can be unfeatured
        $featuredReview = Review::factory()->create([
            'product_id' => $product->id,
            'is_approved' => true,
            'is_featured' => true,
        ]);
        $this->assertFalse($featuredReview->canBeFeatured());
        $this->assertTrue($featuredReview->canBeUnfeatured());
    }

    public function test_review_relations(): void
    {
        $product = $this->createTestProduct();
        $user = $this->createTestUser();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        // Test product relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $review->product());
        $this->assertEquals($product->id, $review->product->id);

        // Test user relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $review->user());
        $this->assertEquals($user->id, $review->user->id);

        // Test author relation (alias for user)
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $review->author());
        $this->assertEquals($user->id, $review->author->id);
    }

    public function test_review_translation_management(): void
    {
        $product = $this->createTestProduct();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'title' => 'Original Title',
            'comment' => 'Original Comment',
        ]);

        // Test available locales (should be empty initially)
        $this->assertEmpty($review->getAvailableLocales());

        // Test has translation for
        $this->assertFalse($review->hasTranslationFor('en'));

        // Test get or create translation
        $translation = $review->getOrCreateTranslation('en');
        $this->assertInstanceOf(\App\Models\Translations\ReviewTranslation::class, $translation);
        $this->assertEquals('en', $translation->locale);

        // Test update translation
        $this->assertTrue($review->updateTranslation('en', [
            'title' => 'English Title',
            'comment' => 'English Comment',
        ]));

        // Test available locales now includes 'en'
        $this->assertContains('en', $review->getAvailableLocales());
        $this->assertTrue($review->hasTranslationFor('en'));
    }

    public function test_review_full_display_name(): void
    {
        $product = $this->createTestProduct();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'title' => 'Great Product',
            'rating' => 4,
        ]);

        $displayName = $review->getFullDisplayName();
        $this->assertEquals('Great Product (⭐⭐⭐⭐)', $displayName);
    }

    public function test_review_rating_validation(): void
    {
        $product = $this->createTestProduct();
        
        // Test valid rating
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 3,
        ]);
        $this->assertEquals(3, $review->rating);

        // Test invalid rating (should throw exception)
        $this->expectException(\InvalidArgumentException::class);
        Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 6, // Invalid rating
        ]);
    }

    public function test_review_recent_method(): void
    {
        $product = $this->createTestProduct();
        
        // Test recent review
        $recentReview = Review::factory()->create([
            'product_id' => $product->id,
            'created_at' => now()->subDays(15),
        ]);
        $this->assertTrue($recentReview->isRecent());

        // Test old review
        $oldReview = Review::factory()->create([
            'product_id' => $product->id,
            'created_at' => now()->subDays(45),
        ]);
        $this->assertFalse($oldReview->isRecent());
    }
}