<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Product;
use App\Models\ProductComparison;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_and_casts_configuration_is_locked_down(): void
    {
        // Instantiate the model so we can inspect its guarded configuration without persisting data.
        $model = new ProductComparison;

        // Verify the mass assignable fields cover the session and relation identifiers only.
        self::assertSame([
            'session_id',
            'user_id',
            'product_id',
        ], $model->getFillable());

        // Ensure integer casting is applied to foreign keys and the session identifier remains a string.
        $casts = $model->getCasts();
        self::assertArrayHasKey('session_id', $casts);
        self::assertSame('string', $casts['session_id']);
        self::assertArrayHasKey('user_id', $casts);
        self::assertSame('integer', $casts['user_id']);
        self::assertArrayHasKey('product_id', $casts);
        self::assertSame('integer', $casts['product_id']);
    }

    public function test_relationships_resolve_linked_models(): void
    {
        // Build the related records we will associate with the comparison row.
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Persist a comparison tying the user to the product under a shared session.
        $comparison = ProductComparison::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'session_id' => 'session-123',
        ]);

        // Confirm the belongsTo relations hydrate the expected model instances.
        self::assertTrue($user->is($comparison->user));
        self::assertTrue($product->is($comparison->product));
    }

    public function test_scope_for_session_filters_to_requested_session(): void
    {
        // Prepare two separate sessions with unique identifiers for filtering assertions.
        $targetSession = 'session-target';
        $otherSession = 'session-other';

        // Seed comparisons for both the target and non-target sessions.
        ProductComparison::factory()->count(2)->create(['session_id' => $targetSession]);
        ProductComparison::factory()->count(3)->create(['session_id' => $otherSession]);

        // Execute the scope and verify only comparisons from the requested session are returned.
        $results = ProductComparison::query()->forSession($targetSession)->pluck('session_id')->all();

        self::assertSame([$targetSession, $targetSession], $results);
    }

    public function test_scope_for_user_filters_to_requested_user(): void
    {
        // Create two different customers to ensure the scope properly isolates the intended one.
        $targetUser = User::factory()->create();
        $otherUser = User::factory()->create();

        // Seed comparisons for both users using different session identifiers to avoid collisions.
        ProductComparison::factory()->count(2)->create([
            'user_id'    => $targetUser->id,
            'session_id' => 'session-target',
        ]);
        ProductComparison::factory()->create([
            'user_id'    => $otherUser->id,
            'session_id' => 'session-other',
        ]);

        // Execute the scope and confirm we only receive comparison rows for the requested user.
        $results = ProductComparison::query()->forUser($targetUser->id)->pluck('user_id')->unique()->all();

        self::assertSame([$targetUser->id], $results);
    }
}
