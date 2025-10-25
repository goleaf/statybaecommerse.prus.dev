<?php declare(strict_types=1);

namespace Tests\Models;

use App\Models\News;
use App\Models\NewsComment;
use App\Models\Scopes\ApprovedScope;
use App\Models\Scopes\VisibleScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NewsCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_and_casts_configuration(): void
    {
        // Instantiate a fresh model instance to inspect its configuration without hitting the database.
        $model = new NewsComment();

        // Validate that the expected columns can be mass assigned for administrative tooling.
        self::assertSame([
            'news_id',
            'parent_id',
            'author_name',
            'author_email',
            'content',
            'is_approved',
            'is_visible',
            'is_active',
        ], $model->getFillable());

        // Confirm the attribute casting configuration keeps boolean and integer fields strongly typed.
        $casts = $model->getCasts();
        $expectedCastKeys = [
            'news_id',
            'parent_id',
            'is_approved',
            'is_visible',
            'is_active',
        ];

        // Extract only the casts of interest so additional framework defaults do not disturb the comparison.
        $relevantCasts = array_intersect_key($casts, array_flip($expectedCastKeys));

        // Validate the precise cast definitions for each attribute we rely on within business logic.
        self::assertSame([
            'news_id' => 'integer',
            'parent_id' => 'integer',
            'is_approved' => 'boolean',
            'is_visible' => 'boolean',
            'is_active' => 'boolean',
        ], $relevantCasts);
    }

    public function test_local_scopes_filter_expected_records(): void
    {
        // Prepare a base news article to associate with the comments under test.
        $news = News::factory()->create();

        // Create a visible and approved comment that should remain present across all scoped queries.
        $approved = NewsComment::factory()->for($news)->create();

        // Create an unapproved comment that will only appear when explicitly requested.
        $unapproved = NewsComment::factory()->for($news)->create([
            'is_approved' => false,
        ]);

        // Create an invisible comment to exercise the visibility scope behaviour.
        $invisible = NewsComment::factory()->for($news)->create([
            'is_visible' => false,
        ]);

        // Attach a reply to the approved comment to test the top-level helper against nested comments.
        $reply = NewsComment::factory()->for($news)->reply($approved)->create();

        // Collect the identifiers returned by the approved scope while bypassing the global approved filter.
        $approvedIds = NewsComment::query()
            ->withoutGlobalScope(ApprovedScope::class)
            ->approved()
            ->pluck('id')
            ->all();

        // Collect the identifiers returned by the visible scope while bypassing the global visibility filter.
        $visibleIds = NewsComment::query()
            ->withoutGlobalScope(VisibleScope::class)
            ->visible()
            ->pluck('id')
            ->all();

        // Collect the identifiers returned by the top-level scope.
        $topLevelIds = NewsComment::query()
            ->withoutGlobalScopes()
            ->topLevel()
            ->pluck('id')
            ->all();

        // Verify only the approved record is returned by the approved scope.
        self::assertContains($approved->id, $approvedIds);
        self::assertNotContains($unapproved->id, $approvedIds);

        // Ensure the visibility scope excludes hidden comments.
        self::assertContains($approved->id, $visibleIds);
        self::assertNotContains($invisible->id, $visibleIds);

        // Confirm that replies are excluded from the top-level scope result set while all root comments remain.
        self::assertContains($approved->id, $topLevelIds);
        self::assertContains($unapproved->id, $topLevelIds);
        self::assertContains($invisible->id, $topLevelIds);
        self::assertNotContains($reply->id, $topLevelIds);
    }

    public function test_scope_ordered_by_name_sorts_alphabetically(): void
    {
        // Associate the comments with a shared article for deterministic ordering checks.
        $news = News::factory()->create();

        // Create comments with specific author names to validate lexicographical ordering.
        NewsComment::factory()->for($news)->create(['author_name' => 'Zara Zenith']);
        NewsComment::factory()->for($news)->create(['author_name' => 'Alice Aurora']);

        // Retrieve the ordered author names via the dedicated scope.
        $orderedNames = NewsComment::query()
            ->orderedByName()
            ->pluck('author_name')
            ->all();

        // Confirm the scope sorts values in ascending alphabetical order.
        self::assertSame(['Alice Aurora', 'Zara Zenith'], $orderedNames);
    }

    public function test_is_reply_and_has_replies_helpers(): void
    {
        // Seed an article and a parent comment to attach replies to.
        $news = News::factory()->create();
        $parent = NewsComment::factory()->for($news)->create();

        // Persist a reply to exercise both helper methods.
        $reply = NewsComment::factory()->for($news)->reply($parent)->create();

        // Refresh the models from the database to ensure relation counts are accurate.
        $parent = $parent->fresh();
        $reply = $reply->fresh();

        // The reply should report true when checking if it has a parent.
        self::assertTrue($reply->isReply());

        // The parent should confirm that it has at least one reply associated with it.
        self::assertTrue($parent->hasReplies());
    }

    public function test_is_active_defaults_follow_visibility(): void
    {
        // Persist a comment without explicitly setting the active flag so the lifecycle hook assigns the default.
        $defaultActive = NewsComment::factory()->create();

        // Persist a hidden comment to ensure the lifecycle hook mirrors the visibility flag.
        $hidden = NewsComment::factory()->create([
            'is_visible' => false,
        ]);

        // Retrieve the models without global scopes to inspect the raw flag values after creation.
        $defaultActive = NewsComment::withoutGlobalScopes()->findOrFail($defaultActive->id);
        $hidden = NewsComment::withoutGlobalScopes()->findOrFail($hidden->id);

        // A missing visibility flag should default the active flag to true for backwards compatibility.
        self::assertTrue($defaultActive->is_active);

        // Hidden comments should automatically have the active flag disabled.
        self::assertFalse($hidden->is_active);
    }
}

