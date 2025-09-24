<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use App\Models\UserProductInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserProductInteractionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Product $product;

    private UserProductInteraction $interaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
        $this->interaction = UserProductInteraction::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'interaction_type' => 'view',
            'rating' => 4.5,
            'count' => 3,
        ]);
    }

    public function test_belongs_to_user(): void
    {
        $this->assertInstanceOf(User::class, $this->interaction->user);
        $this->assertEquals($this->user->id, $this->interaction->user->id);
    }

    public function test_belongs_to_product(): void
    {
        $this->assertInstanceOf(Product::class, $this->interaction->product);
        $this->assertEquals($this->product->id, $this->interaction->product->id);
    }

    public function test_fillable_attributes(): void
    {
        $fillable = [
            'user_id',
            'product_id',
            'interaction_type',
            'rating',
            'count',
            'first_interaction',
            'last_interaction',
        ];

        $this->assertEquals($fillable, $this->interaction->getFillable());
    }

    public function test_casts(): void
    {
        $this->assertIsFloat($this->interaction->rating);
        $this->assertIsInt($this->interaction->count);
        $this->assertInstanceOf(\Carbon\Carbon::class, $this->interaction->first_interaction);
        $this->assertInstanceOf(\Carbon\Carbon::class, $this->interaction->last_interaction);
    }

    public function test_scope_by_type(): void
    {
        $viewInteractions = UserProductInteraction::byType('view')->get();
        $clickInteractions = UserProductInteraction::byType('click')->get();

        $this->assertCount(1, $viewInteractions);
        $this->assertCount(0, $clickInteractions);
        $this->assertTrue($viewInteractions->contains($this->interaction));
    }

    public function test_scope_by_user(): void
    {
        $otherUser = User::factory()->create();
        $otherInteraction = UserProductInteraction::factory()->create([
            'user_id' => $otherUser->id,
            'product_id' => $this->product->id,
        ]);

        $userInteractions = UserProductInteraction::byUser($this->user->id)->get();
        $otherUserInteractions = UserProductInteraction::byUser($otherUser->id)->get();

        $this->assertCount(1, $userInteractions);
        $this->assertCount(1, $otherUserInteractions);
        $this->assertTrue($userInteractions->contains($this->interaction));
        $this->assertTrue($otherUserInteractions->contains($otherInteraction));
    }

    public function test_scope_by_product(): void
    {
        $otherProduct = Product::factory()->create();
        $otherInteraction = UserProductInteraction::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $otherProduct->id,
        ]);

        $productInteractions = UserProductInteraction::byProduct($this->product->id)->get();
        $otherProductInteractions = UserProductInteraction::byProduct($otherProduct->id)->get();

        $this->assertCount(1, $productInteractions);
        $this->assertCount(1, $otherProductInteractions);
        $this->assertTrue($productInteractions->contains($this->interaction));
        $this->assertTrue($otherProductInteractions->contains($otherInteraction));
    }

    public function test_scope_with_min_count(): void
    {
        $lowCountInteraction = UserProductInteraction::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'count' => 1,
        ]);

        $highCountInteractions = UserProductInteraction::withMinCount(3)->get();
        $lowCountInteractions = UserProductInteraction::withMinCount(5)->get();

        $this->assertCount(1, $highCountInteractions);
        $this->assertCount(0, $lowCountInteractions);
        $this->assertTrue($highCountInteractions->contains($this->interaction));
    }

    public function test_scope_with_min_rating(): void
    {
        $lowRatingInteraction = UserProductInteraction::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'rating' => 2.0,
        ]);

        $highRatingInteractions = UserProductInteraction::withMinRating(4.0)->get();
        $veryHighRatingInteractions = UserProductInteraction::withMinRating(5.0)->get();

        $this->assertCount(1, $highRatingInteractions);
        $this->assertCount(0, $veryHighRatingInteractions);
        $this->assertTrue($highRatingInteractions->contains($this->interaction));
    }

    public function test_scope_recent(): void
    {
        $oldInteraction = UserProductInteraction::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'last_interaction' => now()->subDays(40),
        ]);

        $recentInteractions = UserProductInteraction::recent(30)->get();
        $veryRecentInteractions = UserProductInteraction::recent(1)->get();

        $this->assertCount(1, $recentInteractions);
        $this->assertCount(1, $veryRecentInteractions);
        $this->assertTrue($recentInteractions->contains($this->interaction));
        $this->assertTrue($veryRecentInteractions->contains($this->interaction));
    }

    public function test_increment_interaction(): void
    {
        $originalCount = $this->interaction->count;
        $originalLastInteraction = $this->interaction->last_interaction;

        $this->interaction->incrementInteraction(4.8);

        $this->interaction->refresh();
        $this->assertEquals($originalCount + 1, $this->interaction->count);
        $this->assertEquals(4.8, $this->interaction->rating);
        $this->assertTrue($this->interaction->last_interaction->gt($originalLastInteraction));
    }

    public function test_increment_interaction_without_rating(): void
    {
        $originalCount = $this->interaction->count;
        $originalRating = $this->interaction->rating;

        $this->interaction->incrementInteraction();

        $this->interaction->refresh();
        $this->assertEquals($originalCount + 1, $this->interaction->count);
        $this->assertEquals($originalRating, $this->interaction->rating);
    }

    public function test_can_create_interaction(): void
    {
        $interaction = UserProductInteraction::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'interaction_type' => 'click',
            'rating' => 3.5,
            'count' => 1,
            'first_interaction' => now(),
            'last_interaction' => now(),
        ]);

        $this->assertDatabaseHas('user_product_interactions', [
            'id' => $interaction->id,
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'interaction_type' => 'click',
            'rating' => 3.5,
            'count' => 1,
        ]);
    }

    public function test_can_update_interaction(): void
    {
        $this->interaction->update([
            'interaction_type' => 'purchase',
            'rating' => 5.0,
            'count' => 10,
        ]);

        $this->interaction->refresh();
        $this->assertEquals('purchase', $this->interaction->interaction_type);
        $this->assertEquals(5.0, $this->interaction->rating);
        $this->assertEquals(10, $this->interaction->count);
    }

    public function test_can_delete_interaction(): void
    {
        $interactionId = $this->interaction->id;
        $this->interaction->delete();

        $this->assertDatabaseMissing('user_product_interactions', [
            'id' => $interactionId,
        ]);
    }

    public function test_interaction_type_validation(): void
    {
        $validTypes = ['view', 'click', 'add_to_cart', 'purchase', 'review', 'share', 'favorite', 'compare'];

        foreach ($validTypes as $type) {
            $interaction = UserProductInteraction::factory()->create([
                'interaction_type' => $type,
            ]);

            $this->assertEquals($type, $interaction->interaction_type);
        }
    }

    public function test_rating_range(): void
    {
        $interaction = UserProductInteraction::factory()->create([
            'rating' => 0.0,
        ]);
        $this->assertEquals(0.0, $interaction->rating);

        $interaction->update(['rating' => 5.0]);
        $this->assertEquals(5.0, $interaction->rating);
    }

    public function test_count_positive(): void
    {
        $interaction = UserProductInteraction::factory()->create([
            'count' => 1,
        ]);
        $this->assertEquals(1, $interaction->count);

        $interaction->update(['count' => 100]);
        $this->assertEquals(100, $interaction->count);
    }

    public function test_timestamps_are_set(): void
    {
        $this->assertNotNull($this->interaction->created_at);
        $this->assertNotNull($this->interaction->updated_at);
    }

    public function test_soft_deletes_are_not_used(): void
    {
        $this->assertFalse(in_array('SoftDeletes', class_uses($this->interaction)));
    }

    public function test_model_uses_has_factory(): void
    {
        $this->assertTrue(in_array('HasFactory', class_uses($this->interaction)));
    }
}
