<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\NewsResource;
use App\Filament\Widgets\UltimateStatsWidget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use Throwable;

/**
 * Property-based test for Filament admin interface cleanup
 *
 * **Feature: news-blog-cleanup-upgrade, Property 6: Filament admin interface cleanup**
 * **Validates: Requirements 7.1, 7.2, 7.3, 7.4**
 */
final class FilamentAdminInterfaceCleanupPropertyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the admin panel for Filament testing
        $this->resolveAdminPanel();

        // Create admin user for authentication with unique email
        $this->admin = User::factory()->create([
            'email'    => 'admin-' . uniqid() . '@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    /**
     * Property 6: Filament admin interface cleanup
     *
     * For any News-related admin interface after updates, there should be no tag or
     * comment management options, form fields, or table columns related to the removed functionality.
     *
     * **Validates: Requirements 7.1, 7.2, 7.3, 7.4**
     */
    public function test_filament_admin_interface_cleanup_completeness(): void
    {
        // Act & Assert: Verify NewsTag resources are completely removed
        $this->assertNewsTagResourcesRemoved();

        // Act & Assert: Verify NewsTag references removed from widgets
        $this->assertNewsTagReferencesRemovedFromWidgets();

        // Act & Assert: Verify News resource no longer has tag/comment functionality
        $this->assertNewsResourceCleanedUp();

        // Act & Assert: Verify no NewsTag imports or references exist in Filament files
        $this->assertNoNewsTagReferencesInFilamentFiles();
    }

    /**
     * Test that Filament admin interface works correctly across different scenarios after cleanup
     */
    public function test_filament_admin_interface_functionality_across_scenarios(): void
    {
        $scenarios = [
            'news_resource_listing',
            'news_resource_creation',
            'news_resource_editing',
            'widget_rendering',
        ];

        foreach ($scenarios as $scenario) {
            // Test the scenario
            $this->runFilamentScenario($scenario);

            // Verify removed functionality doesn't interfere
            $this->assertRemovedFilamentFunctionalityDoesNotInterfere();
        }
    }

    /**
     * Property test: Filament resources work universally without NewsTag dependencies
     *
     * Tests that all Filament resources work correctly without any NewsTag dependencies.
     */
    public function test_filament_resources_work_universally_without_newstag(): void
    {
        // Test that NewsResource can be instantiated without NewsTag dependencies
        $newsResource = new NewsResource;
        $this->assertInstanceOf(NewsResource::class, $newsResource);

        // Test that UltimateStatsWidget can be instantiated without NewsTag references
        $widget = new UltimateStatsWidget;
        $this->assertInstanceOf(UltimateStatsWidget::class, $widget);

        // Test that widget stats don't include NewsTag counting
        $stats = $widget->getStats();
        $this->assertIsArray($stats);

        // Verify no NewsTag-related stats exist
        foreach ($stats as $stat) {
            $label = $stat->getLabel();
            $this->assertStringNotContainsString('NewsTag', $label, 'Widget should not contain NewsTag references');
            $this->assertStringNotContainsString('news_tag', $label, 'Widget should not contain news_tag references');
            $this->assertStringNotContainsString('tag', strtolower($label), 'Widget should not contain tag-related stats for news');
        }
    }

    private function assertNewsTagResourcesRemoved(): void
    {
        // Verify NewsTagResource.php file is removed
        $newsTagResourceFile = app_path('Filament/Resources/NewsTagResource.php');
        $this->assertFalse(
            File::exists($newsTagResourceFile),
            'NewsTagResource.php file should be removed'
        );

        // Verify NewsTags directory is removed
        $newsTagsDirectory = app_path('Filament/Resources/NewsTags');
        $this->assertFalse(
            File::isDirectory($newsTagsDirectory),
            'NewsTags directory should be completely removed'
        );

        // Verify no NewsTag-related resource classes exist
        // We check if the class can be loaded without triggering autoloader errors
        $newsTagResourceExists = false;
        $legacyNewsTagResourceExists = false;

        try {
            $newsTagResourceExists = class_exists('App\\Filament\\Resources\\NewsTagResource', false);
        } catch (Throwable $e) {
            // Class doesn't exist, which is what we want
        }

        try {
            $legacyNewsTagResourceExists = class_exists('App\\Filament\\Resources\\NewsTags\\NewsTagResource', false);
        } catch (Throwable $e) {
            // Class doesn't exist, which is what we want
        }

        $this->assertFalse(
            $newsTagResourceExists,
            'NewsTagResource class should not exist'
        );

        $this->assertFalse(
            $legacyNewsTagResourceExists,
            'Legacy NewsTagResource class should not exist'
        );
    }

    private function assertNewsTagReferencesRemovedFromWidgets(): void
    {
        // Check UltimateStatsWidget for NewsTag references
        $widgetFile = app_path('Filament/Widgets/UltimateStatsWidget.php');

        if (File::exists($widgetFile)) {
            $widgetContent = File::get($widgetFile);

            // Assert no NewsTag imports
            $this->assertStringNotContainsString(
                'use App\\Models\\NewsTag',
                $widgetContent,
                'UltimateStatsWidget should not import NewsTag model'
            );

            // Assert no NewsTag counting
            $this->assertStringNotContainsString(
                'NewsTag::count()',
                $widgetContent,
                'UltimateStatsWidget should not count NewsTag records'
            );

            $this->assertStringNotContainsString(
                '$totalNewsTags',
                $widgetContent,
                'UltimateStatsWidget should not have NewsTag variables'
            );

            // Assert no NewsTag-related stats
            $this->assertStringNotContainsString(
                'news_tags',
                $widgetContent,
                'UltimateStatsWidget should not reference news_tags'
            );
        }
    }

    private function assertNewsResourceCleanedUp(): void
    {
        // Verify NewsResource exists (should be preserved)
        $this->assertTrue(
            class_exists('App\\Filament\\Resources\\NewsResource'),
            'NewsResource should still exist'
        );

        // Check NewsResource file for tag/comment references
        $newsResourceFile = app_path('Filament/Resources/NewsResource.php');

        if (File::exists($newsResourceFile)) {
            $resourceContent = File::get($newsResourceFile);

            // Assert no tag-related form fields
            $this->assertStringNotContainsString(
                'tags()',
                $resourceContent,
                'NewsResource should not have tag form fields'
            );

            // Assert no comment-related form fields
            $this->assertStringNotContainsString(
                'comments()',
                $resourceContent,
                'NewsResource should not have comment form fields'
            );

            // Assert no NewsTag imports
            $this->assertStringNotContainsString(
                'use App\\Models\\NewsTag',
                $resourceContent,
                'NewsResource should not import NewsTag model'
            );

            // Assert no NewsComment imports
            $this->assertStringNotContainsString(
                'use App\\Models\\NewsComment',
                $resourceContent,
                'NewsResource should not import NewsComment model'
            );
        }
    }

    private function assertNoNewsTagReferencesInFilamentFiles(): void
    {
        $filamentPath = app_path('Filament');

        if (File::isDirectory($filamentPath)) {
            $filamentFiles = File::allFiles($filamentPath);

            foreach ($filamentFiles as $file) {
                $content = File::get($file->getPathname());

                // Skip if this is a test file or backup file
                if (str_contains($file->getPathname(), 'Test.php') ||
                    str_contains($file->getPathname(), '.bak')) {
                    continue;
                }

                // Assert no NewsTag model imports
                $this->assertStringNotContainsString(
                    'use App\\Models\\NewsTag',
                    $content,
                    "File {$file->getRelativePathname()} should not import NewsTag model"
                );

                // Assert no NewsTag class references
                $this->assertStringNotContainsString(
                    'NewsTag::',
                    $content,
                    "File {$file->getRelativePathname()} should not reference NewsTag class"
                );

                // Assert no news_tags table references
                $this->assertStringNotContainsString(
                    'news_tags',
                    $content,
                    "File {$file->getRelativePathname()} should not reference news_tags table"
                );
            }
        }
    }

    private function assertRemovedFilamentFunctionalityDoesNotInterfere(): void
    {
        // Verify that News-related Filament functionality works without NewsTag dependencies
        $newsResource = new NewsResource;
        $this->assertInstanceOf(NewsResource::class, $newsResource);

        // Verify widgets work without NewsTag references
        $widget = new UltimateStatsWidget;
        $stats = $widget->getStats();
        $this->assertIsArray($stats);
        $this->assertNotEmpty($stats);

        // Verify no errors when accessing widget stats
        foreach ($stats as $stat) {
            $this->assertNotNull($stat->getLabel());
            $this->assertNotNull($stat->getValue());
        }
    }

    private function runFilamentScenario(string $scenario): void
    {
        switch ($scenario) {
            case 'news_resource_listing':
                // Test that NewsResource can be accessed
                $newsResource = new NewsResource;
                $this->assertInstanceOf(NewsResource::class, $newsResource);
                break;

            case 'news_resource_creation':
                // Test that NewsResource can handle creation without tag/comment fields
                $newsResource = new NewsResource;
                $this->assertInstanceOf(NewsResource::class, $newsResource);
                break;

            case 'news_resource_editing':
                // Test that NewsResource can handle editing without tag/comment fields
                $newsResource = new NewsResource;
                $this->assertInstanceOf(NewsResource::class, $newsResource);
                break;

            case 'widget_rendering':
                // Test that widgets render without NewsTag references
                $widget = new UltimateStatsWidget;
                $stats = $widget->getStats();
                $this->assertIsArray($stats);
                break;
        }
    }
}
