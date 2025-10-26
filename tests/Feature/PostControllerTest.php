<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ModerationState;
use App\Http\Controllers\PostController;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

/**
 * PostControllerTest
 *
 * End-to-end coverage for the public post controller routes to guarantee
 * pagination, filtering, and related post logic behave as expected.
 */
final class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_filters_featured_posts(): void
    {
        // Create a single author so we can control the relationship graph.
        $author = User::factory()->create();

        // Persist a featured post and a non-featured post for the same author.
        $featuredPost = Post::factory()->for($author)->create([
            'status'                  => 'active',
            'moderation_state'        => ModerationState::Published,
            'submitted_for_review_at' => now()->subDays(2),
            'approved_at'             => now()->subDay(),
            'approved_by_id'          => $author->getKey(),
            'published_at'            => now()->subDay(),
            'featured'                => true,
            'title'                   => 'Featured Guide',
            'excerpt'                 => 'A highlighted article for the landing page.',
        ]);
        Post::factory()->for($author)->create([
            'status'                  => 'active',
            'moderation_state'        => ModerationState::Published,
            'submitted_for_review_at' => now()->subDays(2),
            'approved_at'             => now()->subDay(),
            'approved_by_id'          => $author->getKey(),
            'published_at'            => now()->subHours(12),
            'featured'                => false,
            'title'                   => 'Regular Update',
        ]);

        $this->assertTrue($featuredPost->exists);

        // Double-check the database contains the expected featured entry before invoking the controller.
        $this->assertSame(1, Post::query()->featured()->count());

        // Invoke the controller directly to inspect pagination behaviour.
        $controller = app(PostController::class);
        $request = Request::create('/posts', 'GET', ['featured' => 1]);
        $response = $controller->index($request);

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('posts.index', $response->name());

        /** @var LengthAwarePaginator<int, Post> $paginator */
        $paginator = $response->getData()['posts'];

        // Ensure the paginator contains only the featured post.
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertTrue($featuredPost->isPublished());
        /** @var Collection<int, Post> $items */
        $items = $paginator->getCollection();
        $this->assertCount(1, $items);
        $firstPost = $items->first();
        $this->assertNotNull($firstPost);
        $this->assertTrue($firstPost->is($featuredPost));
    }

    public function test_show_increments_views_and_filters_related_posts(): void
    {
        // Prepare a shared author to ensure all related posts belong to the same user.
        $author = User::factory()->create();

        // Create the primary post with a known view count baseline.
        $post = Post::factory()->for($author)->create([
            'status'                  => 'active',
            'moderation_state'        => ModerationState::Published,
            'submitted_for_review_at' => now()->subDays(3),
            'approved_at'             => now()->subDays(2),
            'approved_by_id'          => $author->getKey(),
            'published_at'            => now()->subDay(),
            'views_count'             => 5,
            'title'                   => 'Primary Article',
        ]);

        // Seed a handful of related posts that should appear in the related list.
        foreach ([
            ['title' => 'Related Insight 1', 'published_at' => now()->subHours(1)],
            ['title' => 'Related Insight 2', 'published_at' => now()->subHours(2)],
            ['title' => 'Related Insight 3', 'published_at' => now()->subHours(3)],
            ['title' => 'Related Insight 4', 'published_at' => now()->subHours(4)],
        ] as $relatedAttributes) {
            Post::factory()->for($author)->create(array_merge($relatedAttributes, [
                'status'                  => 'active',
                'moderation_state'        => ModerationState::Published,
                'submitted_for_review_at' => now()->subDays(3),
                'approved_at'             => now()->subDays(2),
                'approved_by_id'          => $author->getKey(),
            ]));
        }

        // Add a malformed related record that must be filtered out (missing excerpt).
        $invalidRelated = Post::factory()->for($author)->create([
            'status'                  => 'active',
            'moderation_state'        => ModerationState::Published,
            'submitted_for_review_at' => now()->subDays(4),
            'approved_at'             => now()->subDays(3),
            'approved_by_id'          => $author->getKey(),
            'published_at'            => now()->subHours(5),
            'excerpt'                 => '',
            'title'                   => 'Broken Draft',
        ]);

        // Ensure the related-post query will have multiple records to evaluate.
        $this->assertIsInt($author->getKey());

        /** @var int $authorId */
        $authorId = $author->getKey();
        $this->assertGreaterThanOrEqual(5, Post::query()->byAuthor($authorId)->count());

        // Request the show page to trigger the controller logic.
        $this->assertTrue($post->isPublished());

        $controller = app(PostController::class);

        /** @var Post|null $freshPost */
        $freshPost = $post->fresh();
        $this->assertInstanceOf(Post::class, $freshPost);

        /** @var Post $freshPost */
        $response = $controller->show($freshPost);

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('posts.show', $response->name());

        // Confirm the primary post view count incremented exactly once.
        $post->refresh();
        $this->assertSame(6, $post->views_count);

        /** @var Collection<int, Post> $relatedPosts */
        $relatedPosts = $response->getData()['relatedPosts'];

        // Only three valid posts should surface and none should match the malformed entry.
        $this->assertInstanceOf(Collection::class, $relatedPosts);
        $this->assertCount(3, $relatedPosts);
        $this->assertFalse($relatedPosts->contains(fn (Post $related): bool => $related->is($invalidRelated)));
        $this->assertTrue($relatedPosts->every(fn (Post $related): bool => $related->excerpt !== ''));

        // Ensure that at least one of the seeded valid posts is present for sanity.
        $this->assertTrue($relatedPosts->contains(fn (Post $related): bool => $related->title === 'Related Insight 1'));
    }

    public function test_show_returns_not_found_for_unpublished_post(): void
    {
        // Draft posts should never be reachable via the public route.
        /** @var Post $draftPost */
        $draftPost = new Post([
            'status' => 'draft',
        ]);

        // Attempting to view the draft should yield a 404 response.
        $this->expectException(NotFoundHttpException::class);

        /** @var PostController $controller */
        $controller = app(PostController::class);

        $controller->show($draftPost);
    }
}
