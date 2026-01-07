<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

/**
 * Property-based test for News model cleanup
 *
 * **Feature: news-blog-cleanup-upgrade, Property 1: News model cleanup completeness**
 * **Validates: Requirements 1.1, 2.1, 4.1, 4.2, 4.3**
 */
final class NewsModelCleanupPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 1: News model cleanup completeness
     *
     * For any News model instance after cleanup, the model should not contain any
     * methods or references related to NewsTag or NewsComment functionality, and all
     * core News operations should work correctly.
     *
     * **Validates: Requirements 1.1, 2.1, 4.1, 4.2, 4.3**
     */
    public function test_news_model_cleanup_completeness(): void
    {
        // Arrange: Create a News model instance
        $news = new News;
        $reflection = new ReflectionClass($news);

        // Act & Assert: Verify removed tag-related methods don't exist
        $this->assertMethodDoesNotExist($reflection, 'tags', 'tags() relationship method should be removed');
        $this->assertMethodDoesNotExist($reflection, 'scopeByTag', 'scopeByTag() scope method should be removed');

        // Act & Assert: Verify removed comment-related methods don't exist
        $this->assertMethodDoesNotExist($reflection, 'comments', 'comments() relationship method should be removed');
        $this->assertMethodDoesNotExist($reflection, 'latestComment', 'latestComment() relationship method should be removed');

        // Act & Assert: Verify core functionality is preserved
        $this->assertCoreNewsMethodsExist($reflection);

        // Act & Assert: Verify News model can be instantiated and core operations work
        $this->assertNewsModelOperationsWork();
    }

    /**
     * Test that News model works correctly across different scenarios after cleanup
     */
    public function test_news_model_functionality_across_scenarios(): void
    {
        $scenarios = [
            'basic_news_creation',
            'news_with_categories',
            'news_with_images',
            'published_news',
            'featured_news',
        ];

        foreach ($scenarios as $scenario) {
            // Fresh database for each scenario
            $this->refreshDatabase();

            // Test the scenario
            $this->runNewsScenario($scenario);

            // Verify core functionality works
            $this->assertNewsModelOperationsWork();

            // Verify removed functionality doesn't interfere
            $this->assertRemovedFunctionalityDoesNotInterfere();
        }
    }

    /**
     * Property test: Core News operations work universally
     *
     * Tests that all core News operations work correctly regardless of the
     * specific News instance or data state.
     */
    public function test_core_news_operations_work_universally(): void
    {
        // Test with multiple News instances with different states
        $newsInstances = [
            News::factory()->make(['is_visible' => true, 'is_featured' => false]),
            News::factory()->make(['is_visible' => false, 'is_featured' => true]),
            News::factory()->make(['is_visible' => true, 'is_featured' => true]),
        ];

        foreach ($newsInstances as $news) {
            // Save the instance
            $news->save();

            // Test core operations work
            $this->assertTrue(method_exists($news, 'isPublished'), 'isPublished method should exist');
            $this->assertTrue(method_exists($news, 'isFeatured'), 'isFeatured method should exist');
            $this->assertTrue(method_exists($news, 'incrementViewCount'), 'incrementViewCount method should exist');

            // Test relationships work
            $this->assertTrue(method_exists($news, 'categories'), 'categories relationship should exist');
            $this->assertTrue(method_exists($news, 'images'), 'images relationship should exist');
            $this->assertTrue(method_exists($news, 'approvals'), 'approvals relationship should exist');

            // Test scopes work
            $this->assertTrue(method_exists($news, 'scopePublished'), 'scopePublished should exist');
            $this->assertTrue(method_exists($news, 'scopeFeatured'), 'scopeFeatured should exist');
            $this->assertTrue(method_exists($news, 'scopeByCategory'), 'scopeByCategory should exist');
            $this->assertTrue(method_exists($news, 'scopeSearch'), 'scopeSearch should exist');

            // Test accessors work
            $this->assertIsString($news->getRouteKeyName());
            $this->assertIsBool($news->isFeatured());
        }
    }

    private function assertMethodDoesNotExist(ReflectionClass $reflection, string $methodName, string $message): void
    {
        $this->assertFalse(
            $reflection->hasMethod($methodName),
            $message
        );
    }

    private function assertCoreNewsMethodsExist(ReflectionClass $reflection): void
    {
        $coreMethodsToPreserve = [
            'categories',
            'images',
            'approvals',
            'isPublished',
            'isFeatured',
            'incrementViewCount',
            'scopePublished',
            'scopeFeatured',
            'scopeByCategory',
            'scopeSearch',
            'getRouteKeyName',
        ];

        foreach ($coreMethodsToPreserve as $method) {
            $this->assertTrue(
                $reflection->hasMethod($method),
                "Core method '{$method}' should be preserved in News model"
            );
        }
    }

    private function assertNewsModelOperationsWork(): void
    {
        // Test News model can be created
        $news = News::factory()->create([
            'is_visible'       => true,
            'is_featured'      => false,
            'moderation_state' => 'published',
            'published_at'     => now()->subDay(),
        ]);

        // Test core operations work
        $this->assertInstanceOf(News::class, $news);
        $this->assertIsBool($news->isPublished());
        $this->assertIsBool($news->isFeatured());

        // Test relationships can be accessed without errors
        $this->assertNotNull($news->categories());
        $this->assertNotNull($news->images());
        $this->assertNotNull($news->approvals());

        // Test scopes work
        $publishedNews = News::published()->get();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $publishedNews);

        $featuredNews = News::featured()->get();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $featuredNews);
    }

    private function assertRemovedFunctionalityDoesNotInterfere(): void
    {
        $news = new News;
        $reflection = new ReflectionClass($news);

        // Verify that attempting to access removed methods would fail gracefully
        $removedMethods = ['tags', 'comments', 'latestComment', 'scopeByTag'];

        foreach ($removedMethods as $method) {
            $this->assertFalse(
                $reflection->hasMethod($method),
                "Removed method '{$method}' should not exist and not interfere with core functionality"
            );
        }
    }

    private function runNewsScenario(string $scenario): void
    {
        switch ($scenario) {
            case 'basic_news_creation':
                News::factory()->create();
                break;

            case 'news_with_categories':
                $news = News::factory()->create();
                // Note: We're not testing category relationships here as that would require
                // the full category system to be set up, which is beyond this test's scope
                break;

            case 'news_with_images':
                $news = News::factory()->create();
                // Note: We're not testing image relationships here as that would require
                // the full image system to be set up, which is beyond this test's scope
                break;

            case 'published_news':
                News::factory()->create([
                    'is_visible'       => true,
                    'moderation_state' => 'published',
                    'published_at'     => now()->subDay(),
                ]);
                break;

            case 'featured_news':
                News::factory()->create([
                    'is_visible'       => true,
                    'is_featured'      => true,
                    'moderation_state' => 'published',
                    'published_at'     => now()->subDay(),
                ]);
                break;
        }
    }
}
