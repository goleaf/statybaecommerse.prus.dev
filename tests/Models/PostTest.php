<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_exposes_expected_fillable_and_casts(): void
    {
        // Instantiate the model to read its configuration without hitting the database.
        $model = new Post;

        // Assert the fillable attributes match the whitelist defined on the model.
        self::assertSame([
            'title',
            'title_translations',
            'slug',
            'content',
            'content_translations',
            'excerpt',
            'excerpt_translations',
            'status',
            'moderation_state',
            'submitted_for_review_at',
            'approved_at',
            'approved_by_id',
            'published_at',
            'user_id',
            'meta_title',
            'meta_title_translations',
            'meta_description',
            'meta_description_translations',
            'featured',
            'tags',
            'tags_translations',
            'views_count',
            'likes_count',
            'comments_count',
            'allow_comments',
            'is_pinned',
        ], $model->getFillable());

        // Confirm important casts are registered to keep attribute values consistent.
        self::assertSame(
            'datetime',
            $model->getCasts()['published_at'] ?? null
        );
        self::assertSame(
            'boolean',
            $model->getCasts()['featured'] ?? null
        );
        self::assertSame(
            'array',
            $model->getCasts()['title_translations'] ?? null
        );
    }

    public function test_user_relationship_returns_associated_user(): void
    {
        // Create a post with a dedicated author to validate the belongsTo relationship.
        $post = Post::factory()->for(User::factory())->create();

        // Refresh the relation and ensure the resolved model matches the seeded author.
        $post = $post->fresh(['user']);
        self::assertInstanceOf(User::class, $post->user);
        self::assertSame($post->user_id, $post->user->getKey());
    }

    public function test_scope_ordered_by_name_sorts_using_title_column(): void
    {
        // Seed three posts with deterministic titles to evaluate the scope ordering.
        Post::factory()->create([
            'title' => 'Alpha Title',
            'slug'  => 'alpha-title',
        ]);
        Post::factory()->create([
            'title' => 'Gamma Title',
            'slug'  => 'gamma-title',
        ]);
        Post::factory()->create([
            'title' => 'Beta Title',
            'slug'  => 'beta-title',
        ]);

        // Capture the ordering when sorting ascending and ensure alphabetical order is respected.
        $ascending = Post::withoutGlobalScopes()->orderedByName()->pluck('title')->all();
        self::assertSame(['Alpha Title', 'Beta Title', 'Gamma Title'], $ascending);

        // Also assert descending ordering uses the same column but reverses the direction.
        $descending = Post::withoutGlobalScopes()->orderedByName('desc')->pluck('title')->all();
        self::assertSame(['Gamma Title', 'Beta Title', 'Alpha Title'], $descending);

        // Ensure the created posts remain present to avoid accidental mass deletions in the scope.
        self::assertCount(3, Post::withoutGlobalScopes()->get());
    }

    public function test_engagement_helpers_calculate_scores(): void
    {
        // Persist a post with deterministic engagement metrics for predictable calculations.
        $post = Post::factory()->create([
            'views_count'    => 200,
            'likes_count'    => 30,
            'comments_count' => 10,
        ]);

        // Engagement rate should be computed as ((likes + comments) / views) * 100 with two decimal precision.
        self::assertSame(20.0, $post->getEngagementRate());

        // Popularity score should apply the documented weights: views(1x) + likes(2x) + comments(3x).
        self::assertSame(200 + (30 * 2) + (10 * 3), $post->getPopularityScore());
    }
}
