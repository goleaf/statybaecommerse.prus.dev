<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Enums\ModerationState;
use App\Models\News;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_and_casts_configuration_are_explicit(): void
    {
        // Ensure the fillable attributes remain explicit so mass-assignment stays predictable.
        $news = new News();
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
            'meta_data',
        ], $news->getFillable());

        // Confirm the casts cover every persisted attribute that needs type juggling.
        $this->assertSame([
            'is_visible' => 'boolean',
            'is_featured' => 'boolean',
            'is_breaking' => 'boolean',
            'moderation_state' => ModerationState::class,
            'submitted_for_review_at' => 'datetime',
            'approved_at' => 'datetime',
            'approved_by_id' => 'integer',
            'published_at' => 'datetime',
            'view_count' => 'integer',
            'meta_data' => 'array',
        ], $news->getCasts());
    }

    public function test_relationship_methods_expose_expected_relation_types(): void
    {
        // Using an unsaved instance is enough to validate the relation objects returned by the helpers.
        $news = new News();

        $this->assertInstanceOf(BelongsTo::class, $news->approvedBy());
        $this->assertInstanceOf(HasMany::class, $news->approvals());
        $this->assertInstanceOf(HasOne::class, $news->latestApproval());
        $this->assertInstanceOf(BelongsToMany::class, $news->categories());
        $this->assertInstanceOf(BelongsToMany::class, $news->tags());
        $this->assertInstanceOf(HasMany::class, $news->comments());
        $this->assertInstanceOf(HasOne::class, $news->latestComment());
        $this->assertInstanceOf(HasMany::class, $news->images());
        $this->assertInstanceOf(HasOne::class, $news->latestImage());
    }

    public function test_is_published_accessor_respects_visibility_and_schedule(): void
    {
        // Create a published article that satisfies all constraints.
        $published = News::factory()->create([
            'is_visible' => true,
            'published_at' => now()->subDay(),
            'moderation_state' => ModerationState::Published->value,
        ]);
        $this->assertTrue($published->isPublished());
        $this->assertTrue($published->is_published);

        // Create variations that should fail each constraint individually.
        $hidden = News::factory()->create([
            'is_visible' => false,
            'published_at' => now()->subDay(),
            'moderation_state' => ModerationState::Published->value,
        ]);
        $this->assertFalse($hidden->isPublished());

        $future = News::factory()->create([
            'is_visible' => true,
            'published_at' => now()->addDay(),
            'moderation_state' => ModerationState::Published->value,
        ]);
        $this->assertFalse($future->isPublished());

        $draft = News::factory()->create([
            'is_visible' => true,
            'published_at' => now()->subDay(),
            'moderation_state' => ModerationState::Draft->value,
        ]);
        $this->assertFalse($draft->isPublished());
    }

    public function test_published_scope_and_ordered_by_name_scope_behave_predictably(): void
    {
        // Seed a predictable dataset including null author names.
        $alpha = News::factory()->create([
            'author_name' => 'Alice Author',
            'moderation_state' => ModerationState::Published->value,
            'published_at' => now()->subDay(),
            'is_visible' => true,
        ]);
        $bravo = News::factory()->create([
            'author_name' => 'Bob Writer',
            'moderation_state' => ModerationState::Published->value,
            'published_at' => now()->subHours(2),
            'is_visible' => true,
        ]);
        $charlie = News::factory()->create([
            'author_name' => null,
            'moderation_state' => ModerationState::Published->value,
            'published_at' => now()->subHours(3),
            'is_visible' => true,
        ]);
        News::factory()->create([
            'author_name' => 'Zed Zero',
            'moderation_state' => ModerationState::Draft->value,
            'published_at' => now()->subDay(),
            'is_visible' => true,
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
            'locale' => 'en',
            'title' => 'Sample',
            'slug' => 'sample',
            'summary' => 'Summary',
            'content' => '<script>alert(1)</script><p>Allowed content</p>',
        ]);

        // Refresh the instance to ensure relationships are reloaded for the accessor call.
        $fresh = $news->fresh();
        $this->assertSame('<p>Allowed content</p>', $fresh->content);
    }
}
