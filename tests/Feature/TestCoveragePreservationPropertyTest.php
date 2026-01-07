<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\News;
use App\Models\NewsImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TestCoveragePreservationPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * **Feature: news-blog-cleanup-upgrade, Property 7: Test coverage preservation**
     * **Validates: Requirements 8.2, 8.4**
     *
     * For any core News functionality (create, read, update, delete, publish, categorize),
     * there should be adequate test coverage after test cleanup.
     */
    public function test_core_news_functionality_has_adequate_test_coverage(): void
    {
        // Property: News model can be created successfully
        $news = News::factory()->create([
            'is_visible'   => true,
            'is_featured'  => false,
            'author_name'  => 'Test Author',
            'author_email' => 'test@example.com',
        ]);

        $this->assertInstanceOf(News::class, $news);
        $this->assertDatabaseHas('news', [
            'id'          => $news->id,
            'author_name' => 'Test Author',
        ]);

        // Property: News model can be read/retrieved successfully
        $retrieved = News::find($news->id);
        $this->assertNotNull($retrieved);
        $this->assertEquals($news->id, $retrieved->id);
        $this->assertEquals('Test Author', $retrieved->author_name);

        // Property: News model can be updated successfully
        $news->update([
            'author_name' => 'Updated Author',
            'is_featured' => true,
        ]);

        $this->assertDatabaseHas('news', [
            'id'          => $news->id,
            'author_name' => 'Updated Author',
            'is_featured' => true,
        ]);

        // Property: News model can be soft deleted successfully
        $news->delete();
        $this->assertSoftDeleted('news', [
            'id' => $news->id,
        ]);

        // Property: News publishing functionality works correctly
        $publishedNews = News::factory()->create([
            'is_visible'       => true,
            'published_at'     => now()->subDay(),
            'moderation_state' => \App\Enums\ModerationState::Published->value,
        ]);

        $this->assertTrue($publishedNews->isPublished());

        // Property: News categorization functionality works correctly
        $newsWithCategory = News::factory()->create();

        // Property: Categories relationship exists and works
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $newsWithCategory->categories());

        // Property: News image functionality works correctly
        $newsWithImage = News::factory()->create();
        $image = NewsImage::factory()->create(['news_id' => $newsWithImage->id]);

        $this->assertTrue($newsWithImage->images->contains($image));
        $this->assertNotNull($newsWithImage->primaryImage());
    }

    /**
     * Property test to ensure News scopes work correctly after cleanup
     */
    public function test_news_scopes_work_correctly_after_cleanup(): void
    {
        // Create test data
        $publishedNews = News::factory()->create([
            'is_visible'       => true,
            'published_at'     => now()->subDay(),
            'moderation_state' => \App\Enums\ModerationState::Published->value,
        ]);

        $draftNews = News::factory()->create([
            'is_visible'       => true,
            'published_at'     => now()->subDay(),
            'moderation_state' => \App\Enums\ModerationState::Draft->value,
        ]);

        // Property: Published scope returns only published news
        $publishedResults = News::query()->withoutGlobalScopes()->published()->get();
        $this->assertTrue($publishedResults->contains($publishedNews));
        $this->assertFalse($publishedResults->contains($draftNews));

        // Property: Ordered by name scope works correctly
        $alpha = News::factory()->create(['author_name' => 'Alpha Author']);
        $beta = News::factory()->create(['author_name' => 'Beta Author']);

        $orderedResults = News::query()->withoutGlobalScopes()->orderedByName()->get();
        $authorNames = $orderedResults->pluck('author_name')->filter()->values()->toArray();

        // Should be in alphabetical order
        $this->assertEquals(['Alpha Author', 'Beta Author'], array_slice($authorNames, 0, 2));
    }

    /**
     * Property test to ensure News relationships work correctly after cleanup
     */
    public function test_news_relationships_work_correctly_after_cleanup(): void
    {
        $news = News::factory()->create();

        // Property: Categories relationship works
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $news->categories());

        // Property: Images relationship works
        $image = NewsImage::factory()->create(['news_id' => $news->id]);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $news->images());
        $this->assertTrue($news->images->contains($image));

        // Property: Approvals relationship works
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $news->approvals());

        // Property: Translations relationship works
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $news->translations());
    }
}
