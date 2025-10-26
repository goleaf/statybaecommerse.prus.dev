<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserProductInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserProductInteractionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure the modern fillable and casted attributes stay aligned with the
     * schema contract declared in the model.
     */
    public function test_it_defines_expected_fillable_and_casts(): void
    {
        $interaction = new UserProductInteraction;

        self::assertSame([
            'user_id',
            'product_id',
            'product_variant_id',
            'event',
            'meta',
            'occurred_at',
        ], $interaction->getFillable());

        $casts = $interaction->getCasts();

        self::assertSame('array', $casts['meta'] ?? null);
        self::assertSame('datetime', $casts['occurred_at'] ?? null);
    }

    /**
     * Verify the meta accessor merges legacy scalar columns so older analytics
     * code can continue consuming rating/count data without changes.
     */
    public function test_meta_accessor_merges_legacy_columns(): void
    {
        $interaction = UserProductInteraction::factory()->create([
            'meta' => [
                'rating'            => 4.5,
                'count'             => 7,
                'first_interaction' => now()->subWeek(),
                'last_interaction'  => now()->subDay(),
            ],
        ]);

        $meta = $interaction->meta;

        self::assertSame(4.5, $meta['rating']);
        self::assertSame(7, $meta['count']);
        self::assertSame(4.5, $interaction->rating);
        self::assertSame(7, $interaction->count);
    }

    /**
     * Confirm the legacy interaction_type attribute still proxies the event
     * column so historical queries remain functional.
     */
    public function test_legacy_interaction_type_alias_remains_available(): void
    {
        $interaction = UserProductInteraction::factory()->create([
            'event' => 'click',
        ]);

        self::assertSame('click', $interaction->interaction_type);
    }

    /**
     * Ensure relationships to user, product, and variant models resolve as
     * BelongsTo associations.
     */
    public function test_it_relates_to_user_product_and_variant(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

        $interaction = UserProductInteraction::factory()->create([
            'user_id'            => $user->id,
            'product_id'         => $product->id,
            'product_variant_id' => $variant->id,
        ]);

        self::assertTrue($interaction->user->is($user));
        self::assertTrue($interaction->product->is($product));
        self::assertTrue($interaction->variant->is($variant));
    }

    /**
     * Guarantee orderedByName sorts by the event column so alphabetical
     * listings remain predictable.
     */
    public function test_ordered_by_name_scope_sorts_by_event(): void
    {
        $first = UserProductInteraction::factory()->create(['event' => 'alpha']);
        $second = UserProductInteraction::factory()->create(['event' => 'zulu']);

        $ordered = UserProductInteraction::query()->orderedByName()->pluck('event')->all();

        self::assertSame(['alpha', 'zulu'], $ordered);
        self::assertSame($first->event, $ordered[0]);
    }

    /**
     * Validate the recent scope leverages the occurred_at column when
     * filtering for recent activity.
     */
    public function test_recent_scope_uses_occurred_at(): void
    {
        $recent = UserProductInteraction::factory()->create([
            'occurred_at' => now()->subDay(),
        ]);

        $older = UserProductInteraction::factory()->create([
            'occurred_at' => now()->subMonths(2),
        ]);

        $results = UserProductInteraction::recent(30)->pluck('id')->all();

        self::assertContains($recent->id, $results);
        self::assertNotContains($older->id, $results);
    }

    /**
     * Confirm legacy mass-assignment payloads map correctly to the new schema
     * by saving and hydrating the event/meta fields.
     */
    public function test_fill_translates_legacy_attributes(): void
    {
        $interaction = UserProductInteraction::create([
            'user_id'           => User::factory()->create()->id,
            'product_id'        => Product::factory()->create()->id,
            'interaction_type'  => 'view',
            'rating'            => 3.2,
            'count'             => 5,
            'first_interaction' => now()->subWeek(),
            'last_interaction'  => now()->subDay(),
        ]);

        self::assertSame('view', $interaction->event);
        self::assertSame(3.2, $interaction->rating);
        self::assertSame(5, $interaction->count);
        self::assertSame('view', $interaction->interaction_type);
        self::assertSame('view', $interaction->event);
    }
}
