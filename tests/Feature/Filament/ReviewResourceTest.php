<?php declare(strict_types=1);

use App\Filament\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use function Pest\Laravel\{actingAs, assertDatabaseHas, assertDatabaseMissing};

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->user = User::factory()->create();
    $this->product = Product::factory()->create();
});

it('can render review resource index page', function () {
    actingAs($this->admin)
        ->get(ReviewResource::getUrl('index'))
        ->assertSuccessful();
});

it('can render review resource create page', function () {
    actingAs($this->admin)
        ->get(ReviewResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create review', function () {
    $newData = [
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
        'title' => 'Great Product',
        'content' => 'This product exceeded my expectations. Highly recommended!',
        'rating' => 5,
        'is_approved' => false,
    ];

    actingAs($this->admin)
        ->post(ReviewResource::getUrl('create'), $newData)
        ->assertRedirect();

    assertDatabaseHas('reviews', $newData);
});

it('can render review resource view page', function () {
    $review = Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
    ]);

    actingAs($this->admin)
        ->get(ReviewResource::getUrl('view', ['record' => $review]))
        ->assertSuccessful();
});

it('can render review resource edit page', function () {
    $review = Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
    ]);

    actingAs($this->admin)
        ->get(ReviewResource::getUrl('edit', ['record' => $review]))
        ->assertSuccessful();
});

it('can update review', function () {
    $review = Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
    ]);
    
    $newData = [
        'title' => 'Updated Review Title',
        'content' => 'Updated review content',
        'rating' => 4,
        'is_approved' => true,
    ];

    actingAs($this->admin)
        ->put(ReviewResource::getUrl('edit', ['record' => $review]), array_merge($newData, [
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
        ]))
        ->assertRedirect();

    assertDatabaseHas('reviews', array_merge(['id' => $review->id], $newData));
});

it('can delete review', function () {
    $review = Review::factory()->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
    ]);

    actingAs($this->admin)
        ->delete(ReviewResource::getUrl('edit', ['record' => $review]))
        ->assertRedirect();

    assertDatabaseMissing('reviews', ['id' => $review->id]);
});

it('can list reviews', function () {
    $reviews = Review::factory()->count(5)->create([
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
    ]);

    actingAs($this->admin)
        ->get(ReviewResource::getUrl('index'))
        ->assertSuccessful()
        ->assertSeeText($reviews->first()->title);
});

it('can filter reviews by rating', function () {
    $fiveStarReview = Review::factory()->create([
        'rating' => 5,
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
    ]);
    $oneStarReview = Review::factory()->create([
        'rating' => 1,
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
    ]);

    actingAs($this->admin)
        ->get(ReviewResource::getUrl('index') . '?filter[rating]=5')
        ->assertSuccessful()
        ->assertSeeText($fiveStarReview->title)
        ->assertDontSeeText($oneStarReview->title);
});

it('can filter approved reviews', function () {
    $approvedReview = Review::factory()->create([
        'is_approved' => true,
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
    ]);
    $pendingReview = Review::factory()->create([
        'is_approved' => false,
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
    ]);

    actingAs($this->admin)
        ->get(ReviewResource::getUrl('index') . '?filter[approved]=1')
        ->assertSuccessful()
        ->assertSeeText($approvedReview->title)
        ->assertDontSeeText($pendingReview->title);
});

it('can filter pending reviews', function () {
    $approvedReview = Review::factory()->create([
        'is_approved' => true,
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
    ]);
    $pendingReview = Review::factory()->create([
        'is_approved' => false,
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
    ]);

    actingAs($this->admin)
        ->get(ReviewResource::getUrl('index') . '?filter[pending]=1')
        ->assertSuccessful()
        ->assertSeeText($pendingReview->title)
        ->assertDontSeeText($approvedReview->title);
});

it('can approve review using action', function () {
    $review = Review::factory()->create([
        'is_approved' => false,
        'approved_at' => null,
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
    ]);

    actingAs($this->admin)
        ->post(ReviewResource::getUrl('index') . '/actions/approve', ['record' => $review->id])
        ->assertRedirect();

    assertDatabaseHas('reviews', [
        'id' => $review->id,
        'is_approved' => true,
    ]);
});

it('can reject review using action', function () {
    $review = Review::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
        'product_id' => $this->product->id,
        'user_id' => $this->user->id,
    ]);

    actingAs($this->admin)
        ->post(ReviewResource::getUrl('index') . '/actions/reject', ['record' => $review->id])
        ->assertRedirect();

    assertDatabaseHas('reviews', [
        'id' => $review->id,
        'is_approved' => false,
        'approved_at' => null,
    ]);
});

it('validates required fields when creating review', function () {
    actingAs($this->admin)
        ->post(ReviewResource::getUrl('create'), [])
        ->assertSessionHasErrors(['product_id', 'user_id', 'title', 'content', 'rating']);
});

it('validates rating is within valid range', function () {
    actingAs($this->admin)
        ->post(ReviewResource::getUrl('create'), [
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'title' => 'Test Review',
            'content' => 'Test content',
            'rating' => 6, // Invalid - over 5
        ])
        ->assertSessionHasErrors(['rating']);

    actingAs($this->admin)
        ->post(ReviewResource::getUrl('create'), [
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'title' => 'Test Review',
            'content' => 'Test content',
            'rating' => 0, // Invalid - under 1
        ])
        ->assertSessionHasErrors(['rating']);
});
