<?php declare(strict_types=1);

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->product = Product::factory()->create();
});

it('can create a review', function () {
    $reviewData = [
        'product_id' => $this->product->id,
        'rating' => 5,
        'title' => 'Great product!',
        'content' => 'This product exceeded my expectations.',
        'reviewer_name' => 'John Doe',
        'reviewer_email' => 'john@example.com',
    ];

    $review = Review::create($reviewData);

    expect($review)->toBeInstanceOf(Review::class);
    expect($review->product_id)->toBe($this->product->id);
    expect($review->rating)->toBe(5);
    expect($review->title)->toBe('Great product!');
    expect($review->content)->toBe('This product exceeded my expectations.');
    expect($review->reviewer_name)->toBe('John Doe');
    expect($review->reviewer_email)->toBe('john@example.com');
    expect($review->is_approved)->toBeFalse();
});

it('can approve a review', function () {
    $review = Review::factory()->create([
        'product_id' => $this->product->id,
        'is_approved' => false,
    ]);

    $result = $review->approve();

    expect($result)->toBeTrue();
    expect($review->is_approved)->toBeTrue();
    expect($review->approved_at)->not->toBeNull();
    expect($review->rejected_at)->toBeNull();
});

it('can reject a review', function () {
    $review = Review::factory()->create([
        'product_id' => $this->product->id,
        'is_approved' => true,
    ]);

    $result = $review->reject();

    expect($result)->toBeTrue();
    expect($review->is_approved)->toBeFalse();
    expect($review->rejected_at)->not->toBeNull();
    expect($review->approved_at)->toBeNull();
});

it('validates rating range', function () {
    expect(function () {
        Review::create([
            'product_id' => $this->product->id,
            'rating' => 6, // Invalid rating
            'reviewer_name' => 'John Doe',
            'reviewer_email' => 'john@example.com',
        ]);
    })->toThrow(InvalidArgumentException::class);

    expect(function () {
        Review::create([
            'product_id' => $this->product->id,
            'rating' => 0, // Invalid rating
            'reviewer_name' => 'John Doe',
            'reviewer_email' => 'john@example.com',
        ]);
    })->toThrow(InvalidArgumentException::class);
});

it('can get approved reviews', function () {
    Review::factory()->create(['product_id' => $this->product->id, 'is_approved' => true]);
    Review::factory()->create(['product_id' => $this->product->id, 'is_approved' => false]);

    $approvedReviews = Review::approved()->get();

    expect($approvedReviews)->toHaveCount(1);
    expect($approvedReviews->first()->is_approved)->toBeTrue();
});

it('can get featured reviews', function () {
    Review::factory()->create(['product_id' => $this->product->id, 'is_featured' => true]);
    Review::factory()->create(['product_id' => $this->product->id, 'is_featured' => false]);

    $featuredReviews = Review::featured()->get();

    expect($featuredReviews)->toHaveCount(1);
    expect($featuredReviews->first()->is_featured)->toBeTrue();
});

it('can get reviews by rating', function () {
    Review::factory()->create(['product_id' => $this->product->id, 'rating' => 5]);
    Review::factory()->create(['product_id' => $this->product->id, 'rating' => 3]);

    $highRatedReviews = Review::byRating(5)->get();

    expect($highRatedReviews)->toHaveCount(1);
    expect($highRatedReviews->first()->rating)->toBe(5);
});

it('can get pending reviews', function () {
    Review::factory()->create(['product_id' => $this->product->id, 'is_approved' => false, 'rejected_at' => null]);
    Review::factory()->create(['product_id' => $this->product->id, 'is_approved' => true]);
    Review::factory()->create(['product_id' => $this->product->id, 'is_approved' => false, 'rejected_at' => now()]);

    $pendingReviews = Review::pending()->get();

    expect($pendingReviews)->toHaveCount(1);
    expect($pendingReviews->first()->is_approved)->toBeFalse();
    expect($pendingReviews->first()->rejected_at)->toBeNull();
});

it('can calculate average rating for product', function () {
    Review::factory()->create(['product_id' => $this->product->id, 'rating' => 5, 'is_approved' => true]);
    Review::factory()->create(['product_id' => $this->product->id, 'rating' => 3, 'is_approved' => true]);
    Review::factory()->create(['product_id' => $this->product->id, 'rating' => 4, 'is_approved' => false]); // Not approved

    $averageRating = Review::getAverageRatingForProduct($this->product->id);

    expect($averageRating)->toBe(4.0); // (5 + 3) / 2
});

it('can get review count for product', function () {
    Review::factory()->create(['product_id' => $this->product->id, 'is_approved' => true]);
    Review::factory()->create(['product_id' => $this->product->id, 'is_approved' => true]);
    Review::factory()->create(['product_id' => $this->product->id, 'is_approved' => false]); // Not approved

    $reviewCount = Review::getReviewCountForProduct($this->product->id);

    expect($reviewCount)->toBe(2);
});

it('can get rating distribution for product', function () {
    Review::factory()->create(['product_id' => $this->product->id, 'rating' => 5, 'is_approved' => true]);
    Review::factory()->create(['product_id' => $this->product->id, 'rating' => 5, 'is_approved' => true]);
    Review::factory()->create(['product_id' => $this->product->id, 'rating' => 3, 'is_approved' => true]);
    Review::factory()->create(['product_id' => $this->product->id, 'rating' => 4, 'is_approved' => false]); // Not approved

    $distribution = Review::getRatingDistributionForProduct($this->product->id);

    expect($distribution)->toBe([
        3 => 1,
        5 => 2,
    ]);
});

it('belongs to a product', function () {
    $review = Review::factory()->create(['product_id' => $this->product->id]);

    expect($review->product)->toBeInstanceOf(Product::class);
    expect($review->product->id)->toBe($this->product->id);
});

it('belongs to a user', function () {
    $review = Review::factory()->create(['user_id' => $this->user->id]);

    expect($review->user)->toBeInstanceOf(User::class);
    expect($review->user->id)->toBe($this->user->id);
});

it('has author relationship', function () {
    $review = Review::factory()->create(['user_id' => $this->user->id]);

    expect($review->author)->toBeInstanceOf(User::class);
    expect($review->author->id)->toBe($this->user->id);
});


