<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Test file removal verification for News model cleanup
 *
 * Feature: news-blog-cleanup-upgrade, Property 1: News model cleanup completeness
 * Validates: Requirements 6.1
 */
final class NewsModelCleanupTest extends TestCase
{
    /**
     * Test that NewsTag and NewsComment model files don't exist after cleanup
     *
     * @test
     */
    public function news_tag_and_comment_model_files_should_not_exist(): void
    {
        $newsTagPath = app_path('Models/NewsTag.php');
        $newsCommentPath = app_path('Models/NewsComment.php');
        $newsTagTranslationPath = app_path('Models/Translations/NewsTagTranslation.php');

        // Verify that the model files have been removed
        $this->assertFileDoesNotExist($newsTagPath, 'NewsTag model file should not exist');
        $this->assertFileDoesNotExist($newsCommentPath, 'NewsComment model file should not exist');
        $this->assertFileDoesNotExist($newsTagTranslationPath, 'NewsTagTranslation model file should not exist');
    }

    /**
     * Test that News model file still exists (should be preserved)
     *
     * @test
     */
    public function news_model_file_should_still_exist(): void
    {
        $newsPath = app_path('Models/News.php');

        $this->assertFileExists($newsPath, 'News model file should still exist');
    }
}
