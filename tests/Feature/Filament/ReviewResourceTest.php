<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReviewResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_review_resource_list_page_renders(): void
    {
        $product = Product::factory()->create();
        Review::factory()->count(3)->create(['product_id' => $product->id]);

        $response = $this->get(route('filament.admin.resources.reviews.index'));

        $response->assertOk();
    }

    public function test_review_resource_create_page_renders(): void
    {
        $response = $this->get(route('filament.admin.resources.reviews.create'));

        $response->assertOk();
    }

    public function test_review_resource_can_create_review(): void
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();

        $reviewData = [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'reviewer_name' => 'John Doe',
            'reviewer_email' => 'john@example.com',
            'rating' => 5,
            'title' => 'Great Product',
            'comment' => 'This product is amazing!',
            'locale' => 'en',
            'is_approved' => false,
            'is_featured' => false,
        ];

        $response = $this->post(route('filament.admin.resources.reviews.store'), $reviewData);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'reviewer_name' => 'John Doe',
            'reviewer_email' => 'john@example.com',
            'rating' => 5,
            'title' => 'Great Product',
            'comment' => 'This product is amazing!',
            'locale' => 'en',
            'is_approved' => false,
            'is_featured' => false,
        ]);
    }

    public function test_review_resource_can_edit_review(): void
    {
        $product = Product::factory()->create();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'title' => 'Original Title',
            'comment' => 'Original Comment',
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'comment' => 'Updated Comment',
            'rating' => 4,
        ];

        $response = $this->put(route('filament.admin.resources.reviews.update', $review), $updateData);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'title' => 'Updated Title',
            'comment' => 'Updated Comment',
            'rating' => 4,
        ]);
    }

    public function test_review_resource_can_delete_review(): void
    {
        $product = Product::factory()->create();
        $review = Review::factory()->create(['product_id' => $product->id]);

        $response = $this->delete(route('filament.admin.resources.reviews.destroy', $review));

        $response->assertRedirect();
        
        $this->assertSoftDeleted('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_review_resource_widgets_are_included(): void
    {
        $product = Product::factory()->create();
        Review::factory()->count(3)->create(['product_id' => $product->id]);

        $response = $this->get(route('filament.admin.resources.reviews.index'));

        $response->assertOk();
        // Widgets should be rendered on the index page
        $response->assertSee('Review Statistics');
    }

    public function test_review_resource_bulk_actions(): void
    {
        $product = Product::factory()->create();
        $reviews = Review::factory()->count(3)->create([
            'product_id' => $product->id,
            'is_approved' => false,
            'is_featured' => false,
        ]);

        // Test bulk approve action
        $response = $this->post(route('filament.admin.resources.reviews.bulk-action'), [
            'action' => 'approve',
            'records' => $reviews->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();

        foreach ($reviews as $review) {
            $review->refresh();
            $this->assertTrue($review->is_approved);
            $this->assertNotNull($review->approved_at);
        }

        // Test bulk feature action
        $response = $this->post(route('filament.admin.resources.reviews.bulk-action'), [
            'action' => 'feature',
            'records' => $reviews->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();

        foreach ($reviews as $review) {
            $review->refresh();
            $this->assertTrue($review->is_featured);
        }
    }

    public function test_review_resource_filters(): void
    {
        $product = Product::factory()->create();
        Review::factory()->create(['product_id' => $product->id, 'is_approved' => true]);
        Review::factory()->create(['product_id' => $product->id, 'is_approved' => false]);
        Review::factory()->create(['product_id' => $product->id, 'is_featured' => true]);

        // Test approved filter
        $response = $this->get(route('filament.admin.resources.reviews.index', ['tableFilters[approved][value]' => '1']));
        $response->assertOk();

        // Test featured filter
        $response = $this->get(route('filament.admin.resources.reviews.index', ['tableFilters[featured][value]' => '1']));
        $response->assertOk();

        // Test rating filter
        $response = $this->get(route('filament.admin.resources.reviews.index', ['tableFilters[rating][value]' => '5']));
        $response->assertOk();
    }

    public function test_review_resource_individual_actions(): void
    {
        $product = Product::factory()->create();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'is_approved' => false,
            'is_featured' => false,
        ]);

        // Test approve action
        $response = $this->post(route('filament.admin.resources.reviews.bulk-action'), [
            'action' => 'approve',
            'records' => [$review->id],
        ]);

        $response->assertRedirect();

        $review->refresh();
        $this->assertTrue($review->is_approved);
        $this->assertNotNull($review->approved_at);

        // Test feature action
        $response = $this->post(route('filament.admin.resources.reviews.bulk-action'), [
            'action' => 'feature',
            'records' => [$review->id],
        ]);

        $response->assertRedirect();

        $review->refresh();
        $this->assertTrue($review->is_featured);
    }

    public function test_review_resource_view_page(): void
    {
        $product = Product::factory()->create();
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'title' => 'Test Review',
            'rating' => 5,
        ]);

        $response = $this->get(route('filament.admin.resources.reviews.view', $review));

        $response->assertOk();
        $response->assertSee('Test Review');
    }

    public function test_review_resource_edit_page(): void
    {
        $product = Product::factory()->create();
        $review = Review::factory()->create(['product_id' => $product->id]);

        $response = $this->get(route('filament.admin.resources.reviews.edit', $review));

        $response->assertOk();
    }

    public function test_review_resource_validation(): void
    {
        $invalidData = [
            'rating' => 6, // Invalid rating
            'title' => '', // Required field
            'comment' => '', // Required field
        ];

        $response = $this->post(route('filament.admin.resources.reviews.store'), $invalidData);

        $response->assertSessionHasErrors(['rating', 'title', 'comment']);
    }

    public function test_review_resource_rating_validation(): void
    {
        $product = Product::factory()->create();
        
        // Test rating too low
        $response = $this->post(route('filament.admin.resources.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 0,
            'title' => 'Test',
            'comment' => 'Test comment',
        ]);

        $response->assertSessionHasErrors(['rating']);

        // Test rating too high
        $response = $this->post(route('filament.admin.resources.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 6,
            'title' => 'Test',
            'comment' => 'Test comment',
        ]);

        $response->assertSessionHasErrors(['rating']);
    }
}