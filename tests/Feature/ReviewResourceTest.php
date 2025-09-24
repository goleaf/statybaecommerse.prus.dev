<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\TestCase;

class ReviewResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_reviews(): void
    {
        $review = Review::factory()->create();

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->assertCanSeeTableRecords([$review]);
    }

    public function test_can_create_review(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\CreateReview::class)
            ->fillForm([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'reviewer_name' => 'John Doe',
                'reviewer_email' => 'john@example.com',
                'rating' => 5,
                'title' => 'Great Product',
                'content' => 'This product is amazing!',
                'is_approved' => false,
                'is_featured' => false,
                'locale' => 'en',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'reviewer_name' => 'John Doe',
            'reviewer_email' => 'john@example.com',
            'rating' => 5,
            'title' => 'Great Product',
            'content' => 'This product is amazing!',
            'is_approved' => false,
            'is_featured' => false,
            'locale' => 'en',
        ]);
    }

    public function test_can_edit_review(): void
    {
        $review = Review::factory()->create();

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\EditReview::class, [
            'record' => $review->getRouteKey(),
        ])
            ->fillForm([
                'title' => 'Updated Review',
                'content' => 'Updated content',
                'rating' => 4,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $review->refresh();

        $this->assertEquals('Updated Review', $review->title);
        $this->assertEquals('Updated content', $review->content);
        $this->assertEquals(4, $review->rating);
    }

    public function test_can_view_review(): void
    {
        $review = Review::factory()->create();

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ViewReview::class, [
            'record' => $review->getRouteKey(),
        ])
            ->assertCanSeeText($review->title)
            ->assertCanSeeText($review->content);
    }

    public function test_can_delete_review(): void
    {
        $review = Review::factory()->create();

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->callTableAction('delete', $review);

        $this->assertSoftDeleted('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_can_filter_by_rating(): void
    {
        $highRatedReview = Review::factory()->create(['rating' => 5]);
        $lowRatedReview = Review::factory()->create(['rating' => 2]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->filterTable('rating', 5)
            ->assertCanSeeTableRecords([$highRatedReview])
            ->assertCanNotSeeTableRecords([$lowRatedReview]);
    }

    public function test_can_filter_by_approval_status(): void
    {
        $approvedReview = Review::factory()->create(['is_approved' => true]);
        $pendingReview = Review::factory()->create(['is_approved' => false]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->filterTable('is_approved', true)
            ->assertCanSeeTableRecords([$approvedReview])
            ->assertCanNotSeeTableRecords([$pendingReview]);
    }

    public function test_can_filter_by_featured_status(): void
    {
        $featuredReview = Review::factory()->create(['is_featured' => true]);
        $regularReview = Review::factory()->create(['is_featured' => false]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->filterTable('is_featured', true)
            ->assertCanSeeTableRecords([$featuredReview])
            ->assertCanNotSeeTableRecords([$regularReview]);
    }

    public function test_can_filter_by_product(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $review1 = Review::factory()->create(['product_id' => $product1->id]);
        $review2 = Review::factory()->create(['product_id' => $product2->id]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->filterTable('product_id', $product1->id)
            ->assertCanSeeTableRecords([$review1])
            ->assertCanNotSeeTableRecords([$review2]);
    }

    public function test_can_filter_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $review1 = Review::factory()->create(['user_id' => $user1->id]);
        $review2 = Review::factory()->create(['user_id' => $user2->id]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->filterTable('user_id', $user1->id)
            ->assertCanSeeTableRecords([$review1])
            ->assertCanNotSeeTableRecords([$review2]);
    }

    public function test_can_filter_by_locale(): void
    {
        $ltReview = Review::factory()->create(['locale' => 'lt']);
        $enReview = Review::factory()->create(['locale' => 'en']);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->filterTable('locale', 'lt')
            ->assertCanSeeTableRecords([$ltReview])
            ->assertCanNotSeeTableRecords([$enReview]);
    }

    public function test_can_filter_high_rated_reviews(): void
    {
        $highRatedReview = Review::factory()->create(['rating' => 5]);
        $lowRatedReview = Review::factory()->create(['rating' => 2]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->filterTable('high_rated')
            ->assertCanSeeTableRecords([$highRatedReview])
            ->assertCanNotSeeTableRecords([$lowRatedReview]);
    }

    public function test_can_filter_low_rated_reviews(): void
    {
        $highRatedReview = Review::factory()->create(['rating' => 5]);
        $lowRatedReview = Review::factory()->create(['rating' => 2]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->filterTable('low_rated')
            ->assertCanSeeTableRecords([$lowRatedReview])
            ->assertCanNotSeeTableRecords([$highRatedReview]);
    }

    public function test_can_filter_recent_reviews(): void
    {
        $recentReview = Review::factory()->create(['created_at' => now()]);
        $oldReview = Review::factory()->create(['created_at' => now()->subDays(60)]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->filterTable('recent')
            ->assertCanSeeTableRecords([$recentReview])
            ->assertCanNotSeeTableRecords([$oldReview]);
    }

    public function test_can_approve_review_action(): void
    {
        $review = Review::factory()->create(['is_approved' => false]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->callTableAction('approve', $review)
            ->assertNotified('Review approved successfully');

        $review->refresh();
        $this->assertTrue($review->is_approved);
    }

    public function test_can_reject_review_action(): void
    {
        $review = Review::factory()->create(['is_approved' => false]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->callTableAction('reject', $review)
            ->assertNotified('Review rejected successfully');

        $review->refresh();
        $this->assertFalse($review->is_approved);
        $this->assertNotNull($review->rejected_at);
    }

    public function test_can_feature_review_action(): void
    {
        $review = Review::factory()->create(['is_approved' => true, 'is_featured' => false]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->callTableAction('feature', $review)
            ->assertNotified('Review featured successfully');

        $review->refresh();
        $this->assertTrue($review->is_featured);
    }

    public function test_can_unfeature_review_action(): void
    {
        $review = Review::factory()->create(['is_featured' => true]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->callTableAction('unfeature', $review)
            ->assertNotified('Review unfeatured successfully');

        $review->refresh();
        $this->assertFalse($review->is_featured);
    }

    public function test_can_bulk_approve_reviews(): void
    {
        $reviews = Review::factory()->count(3)->create(['is_approved' => false]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->callTableBulkAction('approve', $reviews)
            ->assertNotified('Selected reviews approved successfully');

        foreach ($reviews as $review) {
            $review->refresh();
            $this->assertTrue($review->is_approved);
        }
    }

    public function test_can_bulk_reject_reviews(): void
    {
        $reviews = Review::factory()->count(3)->create(['is_approved' => false]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->callTableBulkAction('reject', $reviews)
            ->assertNotified('Selected reviews rejected successfully');

        foreach ($reviews as $review) {
            $review->refresh();
            $this->assertFalse($review->is_approved);
            $this->assertNotNull($review->rejected_at);
        }
    }

    public function test_can_bulk_feature_reviews(): void
    {
        $reviews = Review::factory()->count(3)->create(['is_approved' => true, 'is_featured' => false]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->callTableBulkAction('feature', $reviews)
            ->assertNotified('Selected reviews featured successfully');

        foreach ($reviews as $review) {
            $review->refresh();
            $this->assertTrue($review->is_featured);
        }
    }

    public function test_can_bulk_unfeature_reviews(): void
    {
        $reviews = Review::factory()->count(3)->create(['is_featured' => true]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->callTableBulkAction('unfeature', $reviews)
            ->assertNotified('Selected reviews unfeatured successfully');

        foreach ($reviews as $review) {
            $review->refresh();
            $this->assertFalse($review->is_featured);
        }
    }

    public function test_can_search_reviews(): void
    {
        $review1 = Review::factory()->create(['title' => 'Great Product']);
        $review2 = Review::factory()->create(['title' => 'Bad Product']);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->searchTable('Great')
            ->assertCanSeeTableRecords([$review1])
            ->assertCanNotSeeTableRecords([$review2]);
    }

    public function test_can_sort_reviews(): void
    {
        $review1 = Review::factory()->create(['title' => 'A Review']);
        $review2 = Review::factory()->create(['title' => 'B Review']);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ListReviews::class)
            ->sortTable('title')
            ->assertCanSeeTableRecords([$review1, $review2], inOrder: true);
    }

    public function test_form_validation_works(): void
    {
        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\CreateReview::class)
            ->fillForm([
                'product_id' => '', // Required field
                'reviewer_name' => '', // Required field
                'reviewer_email' => 'invalid-email', // Must be valid email
                'rating' => 6, // Must be between 1-5
            ])
            ->call('create')
            ->assertHasFormErrors(['product_id', 'reviewer_name', 'reviewer_email', 'rating']);
    }

    public function test_relationships_are_loaded(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ViewReview::class, [
            'record' => $review->getRouteKey(),
        ])
            ->assertCanSeeText($product->name)
            ->assertCanSeeText($user->name);
    }

    public function test_rating_display_is_correct(): void
    {
        $review = Review::factory()->create(['rating' => 4]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ViewReview::class, [
            'record' => $review->getRouteKey(),
        ])
            ->assertCanSeeText('⭐⭐⭐⭐'); // 4 stars
    }

    public function test_status_display_is_correct(): void
    {
        $approvedReview = Review::factory()->create(['is_approved' => true]);
        $pendingReview = Review::factory()->create(['is_approved' => false, 'rejected_at' => null]);

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ViewReview::class, [
            'record' => $approvedReview->getRouteKey(),
        ])
            ->assertCanSeeText('Approved');

        Livewire::test(\App\Filament\Resources\ReviewResource\Pages\ViewReview::class, [
            'record' => $pendingReview->getRouteKey(),
        ])
            ->assertCanSeeText('Pending');
    }
}
