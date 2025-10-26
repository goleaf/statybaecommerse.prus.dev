<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\UserProductInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserProductInteractionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify an interaction can be created using the modern event/meta schema
     * and persists the legacy columns for compatibility.
     */
    public function test_can_create_interaction_with_event_and_meta(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $timestamp = now();

        $interaction = UserProductInteraction::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'event' => 'view',
            'occurred_at' => $timestamp,
            'meta' => [
                'count' => 3,
                'rating' => 4.25,
                'first_interaction' => $timestamp->clone()->subDay(),
                'last_interaction' => $timestamp,
            ],
        ]);

        $this->assertDatabaseHas('user_product_interactions', [
            'id' => $interaction->id,
            'event' => 'view',
            'count' => 3,
        ]);

        self::assertSame('view', $interaction->interaction_type);
        self::assertSame(3, $interaction->count);
        self::assertSame(4.25, $interaction->rating);
    }

    /**
     * Ensure the scope helpers respect the renamed event column and the new
     * occurred_at timestamp when filtering data.
     */
    public function test_scopes_filter_using_event_and_occurred_at(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $recent = UserProductInteraction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'event' => 'click',
            'meta' => [
                'rating' => 4.8,
                'count' => 8,
            ],
            'occurred_at' => now()->subDay(),
        ]);

        $stale = UserProductInteraction::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'event' => 'view',
            'meta' => [
                'rating' => 1.5,
                'count' => 2,
            ],
            'occurred_at' => now()->subMonths(2),
        ]);

        $eventResults = UserProductInteraction::byType('click')->pluck('id')->all();
        $userResults = UserProductInteraction::byUser($user->id)->pluck('id')->all();
        $productResults = UserProductInteraction::byProduct($product->id)->pluck('id')->all();
        $countResults = UserProductInteraction::withMinCount(5)->pluck('id')->all();
        $ratingResults = UserProductInteraction::withMinRating(4.0)->pluck('id')->all();
        $recentResults = UserProductInteraction::recent(30)->pluck('id')->all();

        self::assertContains($recent->id, $eventResults);
        self::assertContains($recent->id, $userResults);
        self::assertContains($recent->id, $productResults);
        self::assertContains($recent->id, $countResults);
        self::assertContains($recent->id, $ratingResults);
        self::assertContains($recent->id, $recentResults);

        self::assertNotContains($stale->id, $recentResults);
    }

    /**
     * Confirm the increment helper updates both the count column and the
     * consolidated meta payload.
     */
    public function test_increment_interaction_updates_meta_and_count(): void
    {
        $interaction = UserProductInteraction::factory()->create([
            'meta' => [
                'rating' => 2.0,
                'count' => 1,
                'last_interaction' => now()->subDay(),
            ],
        ]);

        $interaction->incrementInteraction(4.0);
        $interaction->refresh();

        self::assertSame(2, $interaction->count);
        self::assertSame(4.0, $interaction->rating);
        self::assertSame(4.0, $interaction->meta['rating']);
        self::assertTrue($interaction->occurred_at->greaterThan(now()->subMinutes(5)));
    }
}
