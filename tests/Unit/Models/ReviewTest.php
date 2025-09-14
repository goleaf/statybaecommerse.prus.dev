<?php declare(strict_types=1);

use App\Models\Review;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->product = Product::factory()->create();
});

it('can create a review', function () {
    $review = Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'title' => 'Great Product',
        'content' => 'This product is amazing!',
        'rating' => 5,
        'is_approved' => false,
    ]);

    expect($review->title)->toBe('Great Product');
    expect($review->content)->toBe('This product is amazing!');
    expect($review->rating)->toBe(5);
    expect($review->is_approved)->toBeFalse();
});

it('belongs to a product', function () {
    $review = Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
    ]);

    expect($review->product->id)->toBe($this->product->id);
});

it('belongs to a user', function () {
    $review = Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
    ]);

    expect($review->user->id)->toBe($this->user->id);
});

it('can be approved', function () {
    $review = Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'is_approved' => false,
        'approved_at' => null,
    ]);

    $review->approve();

    expect($review->fresh()->is_approved)->toBeTrue();
    expect($review->fresh()->approved_at)->not()->toBeNull();
});

it('can be rejected', function () {
    $review = Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'is_approved' => true,
        'approved_at' => now(),
    ]);

    $review->reject();

    expect($review->fresh()->is_approved)->toBeFalse();
    expect($review->fresh()->approved_at)->toBeNull();
});

it('can filter approved reviews', function () {
    $approvedReview = Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'is_approved' => true,
    ]);
    $pendingReview = Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'is_approved' => false,
    ]);

    $approvedReviews = Review::approved()->get();

    expect($approvedReviews)->toHaveCount(1);
    expect($approvedReviews->first()->id)->toBe($approvedReview->id);
});

it('can filter pending reviews', function () {
    $approvedReview = Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'is_approved' => true,
    ]);
    $pendingReview = Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'is_approved' => false,
    ]);

    $pendingReviews = Review::pending()->get();

    expect($pendingReviews)->toHaveCount(1);
    expect($pendingReviews->first()->id)->toBe($pendingReview->id);
});

it('can filter reviews by rating', function () {
    $fiveStarReview = Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'rating' => 5,
        'is_approved' => true,
    ]);
    $threeStarReview = Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'rating' => 3,
        'is_approved' => true,
    ]);

    $fiveStarReviews = Review::byRating(5)->get();

    expect($fiveStarReviews)->toHaveCount(1);
    expect($fiveStarReviews->first()->id)->toBe($fiveStarReview->id);
});

it('validates rating is between 1 and 5', function () {
    expect(fn() => Review::create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'reviewer_name' => 'Test User',
        'reviewer_email' => 'test@example.com',
        'title' => 'Test Review',
        'content' => 'Test content',
        'rating' => 6, // Invalid
    ]))->toThrow(\InvalidArgumentException::class);

    expect(fn() => Review::create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'reviewer_name' => 'Test User',
        'reviewer_email' => 'test@example.com',
        'title' => 'Test Review',
        'content' => 'Test content',
        'rating' => 0, // Invalid
    ]))->toThrow(\InvalidArgumentException::class);
});

it('calculates average rating for a product', function () {
    Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'rating' => 5,
        'is_approved' => true,
    ]);
    Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => User::factory()->create()->id,
        'rating' => 3,
        'is_approved' => true,
    ]);

    $averageRating = Review::where('product_id', $this->product->id)
        ->approved()
        ->avg('rating');

    expect($averageRating)->toBe(4.0);
});
