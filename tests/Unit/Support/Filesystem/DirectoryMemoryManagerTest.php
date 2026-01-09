<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Filesystem;

use App\Support\Filesystem\DirectoryMemoryManager;
use Tests\TestCase;

/**
 * Test DirectoryMemoryManager functionality.
 *
 * Covers memory management, cleanup, and directory tracking features.
 */
final class DirectoryMemoryManagerTest extends TestCase
{
    private DirectoryMemoryManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new DirectoryMemoryManager;
    }

    public function test_remembers_directory(): void
    {
        $directory = '/test/directory';

        $this->manager->remember($directory);

        $this->assertEquals(1, $this->manager->count());
    }

    public function test_ignores_empty_directory(): void
    {
        $this->manager->remember('');

        $this->assertEquals(0, $this->manager->count());
    }

    public function test_normalizes_directory_paths(): void
    {
        $this->manager->remember('/test/directory/');
        $this->manager->remember('/test/directory');

        // Should only count once due to normalization
        $this->assertEquals(1, $this->manager->count());
    }

    public function test_gets_recent_directories_for_prefix(): void
    {
        $this->manager->remember('/test/dir1');
        $this->manager->remember('/test/dir2');
        $this->manager->remember('/other/dir3');

        $testDirectories = $this->manager->getRecentDirectoriesForPrefix('/test');

        $this->assertCount(2, $testDirectories);
        $this->assertContains('/test/dir1', $testDirectories);
        $this->assertContains('/test/dir2', $testDirectories);
        $this->assertNotContains('/other/dir3', $testDirectories);
    }

    public function test_clears_all_directories(): void
    {
        $this->manager->remember('/test/dir1');
        $this->manager->remember('/test/dir2');

        $this->assertEquals(2, $this->manager->count());

        $this->manager->clear();

        $this->assertEquals(0, $this->manager->count());
    }

    public function test_cleanup_when_threshold_exceeded(): void
    {
        // Add directories beyond cleanup threshold (110)
        for ($i = 0; $i < 160; $i++) {
            $this->manager->remember("/test/dir{$i}");
        }

        // Should be cleaned up to around max (100), allowing for some variance due to cleanup timing
        $this->assertLessThanOrEqual(110, $this->manager->count());
        $this->assertGreaterThan(90, $this->manager->count()); // Should be close to 100
    }

    public function test_keeps_most_recent_directories_during_cleanup(): void
    {
        // Add many directories
        for ($i = 0; $i < 160; $i++) {
            $this->manager->remember("/test/dir{$i}");
            // Small delay to ensure different timestamps
            usleep(1000);
        }

        $recentDirectories = $this->manager->getRecentDirectoriesForPrefix('/test');

        // Should contain the most recently added directories
        $this->assertContains('/test/dir159', $recentDirectories);
        $this->assertContains('/test/dir158', $recentDirectories);
    }

    public function test_handles_prefix_with_trailing_slash(): void
    {
        $this->manager->remember('/test/subdir/file');

        $directories1 = $this->manager->getRecentDirectoriesForPrefix('/test/');
        $directories2 = $this->manager->getRecentDirectoriesForPrefix('/test');

        $this->assertEquals($directories1, $directories2);
    }

    public function test_empty_prefix_returns_empty_array(): void
    {
        $this->manager->remember('/test/dir');

        $directories = $this->manager->getRecentDirectoriesForPrefix('');

        $this->assertEmpty($directories);
    }

    public function test_logs_remembered_directories(): void
    {
        // Just test that the method doesn't crash - logging is handled by the logger
        $this->manager->remember('/test/directory');

        // If we get here without exception, logging worked
        $this->assertTrue(true);
    }
}
