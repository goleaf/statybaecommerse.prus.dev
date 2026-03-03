<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Enums\ModerationState;
use App\Models\News;
use App\Models\NewsImage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

final class NewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_and_casts_configuration_are_explicit(): void
    {
        // Ensure the fillable attributes remain explicit so mass-assignment stays predictable.
        $news = new News;
        $this->assertSame([
            'is_visible',
            'is_featured',
            'is_breaking',
            'moderation_state',
            'submitted_for_review_at',
            'approved_at',
            'approved_by_id',
            'published_at',
            'author_name',
            'author_email',
            'view_count',
        ], $news->getFillable());

        // Confirm the casts cover every persisted attribute that needs type juggling.
        $this->assertSame([
            'id'                      => 'int',
            'is_visible'              => 'boolean',
            'is_featured'             => 'boolean',
            'is_breaking'             => 'boolean',
            'moderation_state'        => ModerationState::class,
            'submitted_for_review_at' => 'datetime',
            'approved_at'             => 'datetime',
            'approved_by_id'          => 'integer',
            'published_at'            => 'datetime',
            'view_count'              => 'integer',
            'deleted_at'              => 'datetime',
        ], $news->getCasts());
    }

    public function test_relationship_methods_expose_expected_relation_types(): void
    {
        // Using an unsaved instance is enough to validate the relation objects returned by the helpers.
        $news = new News;

        $this->assertInstanceOf(BelongsTo::class, $news->approvedBy());
        $this->assertInstanceOf(HasMany::class, $news->approvals());
        $this->assertInstanceOf(HasOne::class, $news->latestApproval());
        $this->assertInstanceOf(BelongsToMany::class, $news->categories());
        $this->assertInstanceOf(HasMany::class, $news->images());
        $this->assertInstanceOf(HasOne::class, $news->latestImage());
    }

    public function test_is_published_accessor_respects_visibility_and_schedule(): void
    {
        // Create a published article that satisfies all constraints.
        $published = News::factory()->create([
            'is_visible'       => true,
            'published_at'     => now()->subDay(),
            'moderation_state' => ModerationState::Published->value,
        ]);
        $this->assertTrue($published->isPublished());
        $this->assertTrue($published->is_published);

        // Create variations that should fail each constraint individually.
        $hidden = News::factory()->create([
            'is_visible'       => false,
            'published_at'     => now()->subDay(),
            'moderation_state' => ModerationState::Published->value,
        ]);
        $this->assertFalse($hidden->isPublished());

        $future = News::factory()->create([
            'is_visible'       => true,
            'published_at'     => now()->addDay(),
            'moderation_state' => ModerationState::Published->value,
        ]);
        $this->assertFalse($future->isPublished());

        $draft = News::factory()->create([
            'is_visible'       => true,
            'published_at'     => now()->subDay(),
            'moderation_state' => ModerationState::Draft->value,
        ]);
        $this->assertFalse($draft->isPublished());
    }

    public function test_published_scope_and_ordered_by_name_scope_behave_predictably(): void
    {
        // Seed a predictable dataset including null author names.
        $alpha = News::factory()->create([
            'author_name'      => 'Alice Author',
            'moderation_state' => ModerationState::Published->value,
            'published_at'     => now()->subDay(),
            'is_visible'       => true,
        ]);
        $bravo = News::factory()->create([
            'author_name'      => 'Bob Writer',
            'moderation_state' => ModerationState::Published->value,
            'published_at'     => now()->subHours(2),
            'is_visible'       => true,
        ]);
        $charlie = News::factory()->create([
            'author_name'      => null,
            'moderation_state' => ModerationState::Published->value,
            'published_at'     => now()->subHours(3),
            'is_visible'       => true,
        ]);
        News::factory()->create([
            'author_name'      => 'Zed Zero',
            'moderation_state' => ModerationState::Draft->value,
            'published_at'     => now()->subDay(),
            'is_visible'       => true,
        ]);

        // Only published, visible, and scheduled records should remain after applying the scope.
        $ids = News::query()
            ->withoutGlobalScopes()
            ->published()
            ->orderedByName()
            ->pluck('id')
            ->all();

        $this->assertSame([$alpha->id, $bravo->id, $charlie->id], $ids);
    }

    public function test_content_accessor_returns_sanitized_translation_value(): void
    {
        // Prepare a news item with a translation that contains unwanted markup.
        $news = News::factory()->create();
        $news->translations()->create([
            'locale'  => 'en',
            'title'   => 'Sample',
            'slug'    => 'sample',
            'summary' => 'Summary',
            'content' => '<script>alert(1)</script><p>Allowed content</p>',
        ]);

        // Refresh the instance to ensure relationships are reloaded for the accessor call.
        $fresh = $news->fresh();
        $this->assertSame('<p>Allowed content</p>', $fresh->content);
    }

    public function test_primary_image_helpers_prioritise_featured_assets(): void
    {
        // Seed a published article with translations so the helper can resolve locale-aware fields.
        $news = News::factory()->create([
            'is_visible'       => true,
            'published_at'     => now()->subDay(),
            'moderation_state' => ModerationState::Published->value,
        ]);
        $news->translations()->create([
            'locale'  => 'lt',
            'title'   => 'Primary Image Article',
            'slug'    => 'primary-image-article',
            'summary' => 'Summary copy',
            'content' => 'Body copy',
        ]);

        // Attach two images where the featured flag should determine the preferred item.
        NewsImage::factory()->create([
            'news_id'     => $news->id,
            'is_featured' => false,
            'sort_order'  => 5,
            'file_path'   => 'news-images/non-featured.jpg',
        ]);
        $featured = NewsImage::factory()->create([
            'news_id'     => $news->id,
            'is_featured' => true,
            'sort_order'  => 10,
            'file_path'   => 'news-images/featured.jpg',
        ]);

        $fresh = $news->fresh('images');
        $this->assertSame($featured->id, $fresh->primaryImage()?->id);
        $this->assertSame($featured->url, $fresh->getPrimaryImageUrl());
        $this->assertSame($featured->thumbnail_url, $fresh->getPrimaryImageUrl(true));
        $this->assertTrue($fresh->hasPrimaryImage());

        // Ensure the helper gracefully falls back when no assets are attached.
        $empty = News::factory()->create([
            'is_visible'       => true,
            'published_at'     => now()->subDay(),
            'moderation_state' => ModerationState::Published->value,
        ]);
        $empty->translations()->create([
            'locale'  => 'lt',
            'title'   => 'No Image Article',
            'slug'    => 'no-image-article',
            'summary' => 'Summary copy',
            'content' => 'Body copy',
        ]);

        $this->assertNull($empty->primaryImage());
        $this->assertNull($empty->getPrimaryImageUrl());
        $this->assertNull($empty->getPrimaryImageUrl(true));
        $this->assertFalse($empty->hasPrimaryImage());
    }

    public function test_is_ready_for_frontend_requires_full_content_stack(): void
    {
        // Start with a published article that is missing the imagery requirement.
        $news = News::factory()->create([
            'is_visible'       => true,
            'published_at'     => now()->subDay(),
            'moderation_state' => ModerationState::Published->value,
        ]);
        $news->translations()->create([
            'locale'  => 'lt',
            'title'   => 'Display Ready Article',
            'slug'    => 'display-ready-article',
            'summary' => 'Summary copy',
            'content' => 'Body copy',
        ]);

        $this->assertFalse($news->isReadyForFrontend());

        // Add an image so the record satisfies the storefront display requirements.
        NewsImage::factory()->create([
            'news_id'     => $news->id,
            'is_featured' => true,
            'sort_order'  => 1,
            'file_path'   => 'news-images/ready.jpg',
        ]);

        $this->assertTrue($news->fresh('images')->isReadyForFrontend());

        // Toggle visibility to ensure the helper respects moderation and visibility controls.
        $news->update(['is_visible' => false]);
        $this->assertFalse($news->fresh()->isReadyForFrontend());
    }
}

// Property-based tests for News model cleanup
describe('News Model Cleanup Property Tests', function () {
    /**
     * **Feature: news-blog-cleanup-upgrade, Property 1: News model cleanup completeness**
     * **Validates: Requirements 1.1, 2.1, 4.1, 4.2, 4.3**
     *
     * For any News model instance after cleanup, the model should not contain any methods
     * or references related to NewsTag or NewsComment functionality, and all core News
     * operations should work correctly.
     */
    it('ensures News model has no tag or comment functionality', function () {
        // Property: News model should not have any tag or comment related methods
        $newsModel = new News;
        $reflection = new ReflectionClass($newsModel);

        // Get all public methods
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $methodNames = array_map(fn ($method) => $method->getName(), $methods);

        // Property: No tag-related methods should exist
        $tagMethods = array_filter($methodNames, fn ($name) => str_contains(strtolower($name), 'tag') ||
            str_contains(strtolower($name), 'tags')
        );
        expect($tagMethods)->toBeEmpty('News model should not contain any tag-related methods');

        // Property: No comment-related methods should exist
        $commentMethods = array_filter($methodNames, fn ($name) => str_contains(strtolower($name), 'comment') ||
            str_contains(strtolower($name), 'comments')
        );
        expect($commentMethods)->toBeEmpty('News model should not contain any comment-related methods');

        // Property: Core relationship methods should exist
        expect(in_array('categories', $methodNames))->toBeTrue('News model should have categories relationship');
        expect(in_array('images', $methodNames))->toBeTrue('News model should have images relationship');
        expect(in_array('approvals', $methodNames))->toBeTrue('News model should have approvals relationship');
        expect(in_array('translations', $methodNames))->toBeTrue('News model should have translations relationship');

        // Property: Core functionality methods should exist
        expect(in_array('isPublished', $methodNames))->toBeTrue('News model should have isPublished method');
        expect(in_array('isFeatured', $methodNames))->toBeTrue('News model should have isFeatured method');
        expect(in_array('incrementViewCount', $methodNames))->toBeTrue('News model should have incrementViewCount method');
        expect(in_array('isReadyForFrontend', $methodNames))->toBeTrue('News model should have isReadyForFrontend method');
    });
});
