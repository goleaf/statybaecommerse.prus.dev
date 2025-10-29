<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\UserProductInteraction;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserProductInteractionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create shared fixtures once per test so scope expectations reuse the same subjects.
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create();
    }

    /**
     * Convenience helper for creating interactions with consistent baseline attributes.
     */
    private function createInteraction(array $overrides = []): UserProductInteraction
    {
        // Merge caller overrides with the known-good defaults to keep assertions concise.
        return UserProductInteraction::factory()->create(array_merge([
            'user_id'    => $this->user->id,
            'product_id' => $this->product->id,
        ], $overrides));
    }

    /**
     * Verify an interaction can be created using the modern event/meta schema
     * and persists the legacy columns for compatibility.
     */
    public function test_can_create_interaction_with_event_and_meta(): void
    {
        // Freeze the clock so persisted timestamps and assertions line up reliably.
        $timestamp = CarbonImmutable::parse('2024-05-01 10:15:00');
        Date::setTestNow($timestamp);

        $interaction = $this->createInteraction([
            'event'       => 'view',
            'occurred_at' => $timestamp,
            'meta'        => [
                'count'             => 3,
                'rating'            => 4.25,
                'first_interaction' => $timestamp->clone()->subDay(),
                'last_interaction'  => $timestamp,
            ],
        ]);

        $this->assertDatabaseHas('user_product_interactions', [
            'id'    => $interaction->id,
            'event' => 'view',
            'count' => 3,
        ]);

        self::assertSame('view', $interaction->interaction_type);
        self::assertSame(3, $interaction->count);
        self::assertSame(4.25, $interaction->rating);
        self::assertSame(4.25, $interaction->meta['rating']);

        // Always reset the frozen clock so subsequent tests can opt into their own time controls.
        Date::setTestNow();
    }

    /**
     * Ensure the scope helpers respect the renamed event column and the new
     * occurred_at timestamp when filtering data.
     */
    public function test_scopes_filter_using_event_and_occurred_at(): void
    {
        $recent = $this->createInteraction([
            'event'      => 'click',
            'meta'       => [
                'rating' => 4.8,
                'count'  => 8,
            ],
            'occurred_at' => now()->subDay(),
        ]);

        $stale = $this->createInteraction([
            'event'      => 'view',
            'meta'       => [
                'rating' => 1.5,
                'count'  => 2,
            ],
            'occurred_at' => now()->subMonths(2),
        ]);

        // Iterate the scope callbacks to keep the assertions symmetric and easier to extend.
        $scopeExpectations = [
            'byType'        => static fn (): array => UserProductInteraction::byType('click')->pluck('id')->all(),
            'byUser'        => fn (): array => UserProductInteraction::byUser($this->user->id)->pluck('id')->all(),
            'byProduct'     => fn (): array => UserProductInteraction::byProduct($this->product->id)->pluck('id')->all(),
            'withMinCount'  => static fn (): array => UserProductInteraction::withMinCount(5)->pluck('id')->all(),
            'withMinRating' => static fn (): array => UserProductInteraction::withMinRating(4.0)->pluck('id')->all(),
            'recent'        => static fn (): array => UserProductInteraction::recent(30)->pluck('id')->all(),
        ];

        foreach ($scopeExpectations as $label => $resolver) {
            $ids = $resolver();

            // Confirm the recent interaction stays discoverable regardless of the entry point.
            self::assertContains($recent->id, $ids, sprintf('Scope %s failed to surface the recent interaction.', $label));
        }

        self::assertNotContains($stale->id, $scopeExpectations['recent'](), 'Recent scope should skip stale interactions.');
    }

    /**
     * Confirm the increment helper updates both the count column and the
     * consolidated meta payload.
     */
    public function test_increment_interaction_updates_meta_and_count(): void
    {
        $interaction = $this->createInteraction([
            'meta' => [
                'rating'           => 2.0,
                'count'            => 1,
                'last_interaction' => now()->subDay(),
            ],
        ]);

        // Store the previous occurred_at for a direct comparison after the helper updates it.
        $previousOccurredAt = $interaction->occurred_at;

        $interaction->incrementInteraction(4.0);
        $interaction->refresh();

        self::assertSame(2, $interaction->count);
        self::assertSame(4.0, $interaction->rating);
        self::assertSame(4.0, $interaction->meta['rating']);
        self::assertTrue(
            $interaction->occurred_at->greaterThan($previousOccurredAt),
            'Incrementing should update occurred_at to reflect the latest interaction time.',
        );
    }
}
