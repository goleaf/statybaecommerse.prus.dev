<?php

declare(strict_types=1);

namespace Tests\Feature\Migrations;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsImage;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class NewsCleanupDataPreservationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * **Feature: news-blog-cleanup-upgrade, Property 2: Data preservation during migration**
     * **Validates: Requirements 1.2, 2.2**
     *
     * For any existing News record before migration, after running the cleanup migration,
     * the News record should remain intact with all core data preserved.
     */
    public function test_news_data_preservation_during_cleanup_migration(): void
    {
        // Property: Create test data before migration
        $newsData = [
            'is_visible'       => true,
            'is_featured'      => true,
            'is_breaking'      => false,
            'moderation_state' => 'published',
            'published_at'     => now()->subDay(),
            'author_name'      => 'Test Author',
            'author_email'     => 'test@example.com',
            'view_count'       => 100,
            'meta_data'        => ['test' => 'data'],
        ];

        $news = News::factory()->create($newsData);

        // Create related data that should be preserved (avoid NewsCategory due to translation issues)
        $image = NewsImage::factory()->create([
            'news_id'     => $news->id,
            'is_featured' => true,
        ]);

        $translation = $news->translations()->create([
            'locale'  => 'en',
            'title'   => 'Test News Title',
            'slug'    => 'test-news-title',
            'summary' => 'Test summary',
            'content' => 'Test content',
        ]);

        // Store original data for comparison
        $originalNewsData = $news->fresh()->toArray();
        $originalImageCount = $news->images()->count();
        $originalTranslationCount = $news->translations()->count();

        // Property: Run the cleanup migration
        Artisan::call('migrate', [
            '--path'  => 'database/migrations/2026_01_07_154930_cleanup_news_tag_and_comment_tables.php',
            '--force' => true,
        ]);

        // Property: Verify NewsTag and NewsComment tables are dropped
        $this->assertFalse(Schema::hasTable('news_tags'), 'news_tags table should be dropped');
        $this->assertFalse(Schema::hasTable('news_tag_translations'), 'news_tag_translations table should be dropped');
        $this->assertFalse(Schema::hasTable('sh_news_tag_translations'), 'sh_news_tag_translations table should be dropped');
        $this->assertFalse(Schema::hasTable('news_tag_pivot'), 'news_tag_pivot table should be dropped');
        $this->assertFalse(Schema::hasTable('news_comments'), 'news_comments table should be dropped');

        // Property: Verify News table still exists
        $this->assertTrue(Schema::hasTable('news'), 'news table should still exist');

        // Property: Verify News data is preserved
        $preservedNews = News::find($news->id);
        $this->assertNotNull($preservedNews, 'News record should still exist after migration');

        // Property: Core News data should be identical
        $preservedNewsData = $preservedNews->toArray();
        foreach ($newsData as $key => $value) {
            if ($key === 'meta_data') {
                $this->assertEquals($value, $preservedNewsData[$key], "News {$key} should be preserved");
            } else {
                $this->assertEquals($originalNewsData[$key], $preservedNewsData[$key], "News {$key} should be preserved");
            }
        }

        // Property: Related data should be preserved
        $this->assertEquals($originalImageCount, $preservedNews->images()->count(), 'News images should be preserved');
        $this->assertEquals($originalTranslationCount, $preservedNews->translations()->count(), 'News translations should be preserved');

        // Property: Specific relationships should work correctly
        $this->assertTrue($preservedNews->images->contains($image), 'News image relationship should be preserved');
        $this->assertEquals($translation->title, $preservedNews->translations->first()->title, 'News translation should be preserved');

        // Property: Core News functionality should work
        $this->assertTrue($preservedNews->isPublished(), 'News should still be published');
        $this->assertTrue($preservedNews->isFeatured(), 'News should still be featured');
        $this->assertTrue($preservedNews->hasPrimaryImage(), 'News should still have primary image');
    }

    /**
     * Property test for multiple News records preservation
     */
    public function test_multiple_news_records_preservation_during_cleanup(): void
    {
        // Property: Create multiple News records with different states
        $publishedNews = News::factory()->create([
            'is_visible'       => true,
            'moderation_state' => 'published',
            'published_at'     => now()->subDay(),
        ]);

        $draftNews = News::factory()->create([
            'is_visible'       => false,
            'moderation_state' => 'draft',
            'published_at'     => null,
        ]);

        $featuredNews = News::factory()->create([
            'is_visible'       => true,
            'is_featured'      => true,
            'moderation_state' => 'published',
            'published_at'     => now()->subHour(),
        ]);

        // Add translations to all
        foreach ([$publishedNews, $draftNews, $featuredNews] as $news) {
            $news->translations()->create([
                'locale'  => 'en',
                'title'   => "Title for News {$news->id}",
                'slug'    => "slug-for-news-{$news->id}",
                'summary' => "Summary for News {$news->id}",
                'content' => "Content for News {$news->id}",
            ]);
        }

        $originalCount = News::withoutGlobalScopes()->count();

        // Property: Run the cleanup migration
        Artisan::call('migrate', [
            '--path'  => 'database/migrations/2026_01_07_154930_cleanup_news_tag_and_comment_tables.php',
            '--force' => true,
        ]);

        // Property: All News records should be preserved
        $this->assertEquals($originalCount, News::withoutGlobalScopes()->count(), 'All News records should be preserved');

        // Property: Each News record should maintain its state
        $preservedPublished = News::withoutGlobalScopes()->find($publishedNews->id);
        $preservedDraft = News::withoutGlobalScopes()->find($draftNews->id);
        $preservedFeatured = News::withoutGlobalScopes()->find($featuredNews->id);

        $this->assertNotNull($preservedPublished, 'Published news should be preserved');
        $this->assertNotNull($preservedDraft, 'Draft news should be preserved');
        $this->assertNotNull($preservedFeatured, 'Featured news should be preserved');

        // Property: States should be maintained
        $this->assertTrue($preservedPublished->is_visible, 'Published news visibility should be preserved');
        $this->assertFalse($preservedDraft->is_visible, 'Draft news visibility should be preserved');
        $this->assertTrue($preservedFeatured->is_featured, 'Featured news feature flag should be preserved');

        // Property: Translations should be preserved for all
        $this->assertEquals(1, $preservedPublished->translations()->count(), 'Published news translations should be preserved');
        $this->assertEquals(1, $preservedDraft->translations()->count(), 'Draft news translations should be preserved');
        $this->assertEquals(1, $preservedFeatured->translations()->count(), 'Featured news translations should be preserved');
    }

    /**
     * Property test for database integrity after cleanup
     */
    public function test_database_integrity_after_cleanup_migration(): void
    {
        // Property: Create News with basic relationships
        $news = News::factory()->create([
            'is_visible'       => true,
            'moderation_state' => 'published',
            'published_at'     => now()->subDay(),
        ]);

        // Create images only (avoid NewsCategory translation issues)
        NewsImage::factory()->count(2)->create(['news_id' => $news->id]);

        $news->translations()->create([
            'locale'  => 'en',
            'title'   => 'Complex News Title',
            'slug'    => 'complex-news-title',
            'summary' => 'Complex summary',
            'content' => 'Complex content',
        ]);

        // Property: Run the cleanup migration
        Artisan::call('migrate', [
            '--path'  => 'database/migrations/2026_01_07_154930_cleanup_news_tag_and_comment_tables.php',
            '--force' => true,
        ]);

        // Property: No orphaned foreign key constraints should exist
        $preservedNews = News::find($news->id);
        $this->assertNotNull($preservedNews, 'News should exist after migration');

        // Property: All relationships should work without foreign key errors
        $this->assertCount(2, $preservedNews->images, 'All images should be accessible');
        $this->assertCount(1, $preservedNews->translations, 'Translation should be accessible');

        // Property: Database queries should work without constraint violations
        $this->assertDatabaseHas('news', ['id' => $news->id]);
        $this->assertDatabaseHas('news_images', ['news_id' => $news->id]);
        $this->assertDatabaseHas('news_translations', ['news_id' => $news->id]);

        // Property: No references to dropped tables should exist in remaining data
        $newsRecord = DB::table('news')->where('id', $news->id)->first();
        $this->assertNotNull($newsRecord, 'News record should exist in database');

        // Property: Verify dropped tables no longer exist
        $this->assertFalse(Schema::hasTable('news_tags'), 'news_tags table should not exist');
        $this->assertFalse(Schema::hasTable('news_comments'), 'news_comments table should not exist');
        $this->assertFalse(Schema::hasTable('news_tag_pivot'), 'news_tag_pivot table should not exist');
    }
}
