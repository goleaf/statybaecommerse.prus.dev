<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReviewInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_like_review(): void
    {
        $review = Review::factory()->approved()->create();

        $this->postJson(route('frontend.reviews.like', $review))
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_like_review_once(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->approved()->create();

        $this->actingAs($user);

        $this->postJson(route('frontend.reviews.like', $review))
            ->assertOk()
            ->assertJson([
                'helpful_count'  => 1,
                'reported_count' => 0,
            ]);

        $review->refresh();

        self::assertSame(1, (int) $review->helpful_count);
        self::assertSame([
            $user->id,
        ], array_map('intval', $review->metadata['liked_by'] ?? []));

        $this->postJson(route('frontend.reviews.like', $review))
            ->assertOk()
            ->assertJson([
                'helpful_count' => 1,
            ]);

        $review->refresh();

        self::assertSame(1, (int) $review->helpful_count);
    }

    public function test_authenticated_user_can_report_review_with_reason(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->approved()->create();

        $this->actingAs($user);

        $payload = ['reason' => 'Spam content'];

        $this->postJson(route('frontend.reviews.report', $review), $payload)
            ->assertOk()
            ->assertJson([
                'reported_count' => 1,
            ]);

        $review->refresh();

        self::assertSame(1, (int) $review->reported_count);
        self::assertSame([
            $user->id,
        ], array_map('intval', $review->metadata['reported_by'] ?? []));
        self::assertSame('Spam content', $review->metadata['reported_reasons'][(string) $user->id] ?? null);

        $this->postJson(route('frontend.reviews.report', $review), $payload)
            ->assertOk()
            ->assertJson([
                'reported_count' => 1,
            ]);

        $review->refresh();

        self::assertSame(1, (int) $review->reported_count);
    }
}
