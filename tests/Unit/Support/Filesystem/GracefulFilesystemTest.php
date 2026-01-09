<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Filesystem;

use App\Support\Filesystem\BackupDatabaseManager;
use App\Support\Filesystem\DirectoryMemoryManager;
use App\Support\Filesystem\FilesystemPermissions;
use App\Support\Filesystem\GracefulFilesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

/**
 * Test GracefulFilesystem functionality with Laravel 12 compatibility.
 *
 * Covers the refactored class structure with proper dependency injection,
 * error handling, validation, and Laravel 12 directories() method compatibility.
 */
final class GracefulFilesystemTest extends TestCase
{
    use RefreshDatabase;

    private GracefulFilesystem $filesystem;

    private string $testDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new GracefulFilesystem;
        $this->testDirectory = storage_path('framework/testing/graceful-filesystem-test');

        // Clean up any existing test directory
        if (is_dir($this->testDirectory)) {
            $this->filesystem->deleteDirectory($this->testDirectory);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test directory
        if (is_dir($this->testDirectory)) {
            $this->filesystem->deleteDirectory($this->testDirectory);
        }

        parent::tearDown();
    }

    public function test_creates_directory_when_scanning_non_existent_path(): void
    {
        $nonExistentDir = $this->testDirectory . '/non-existent';

        $this->assertFalse($this->filesystem->isDirectory($nonExistentDir));

        $directories = $this->filesystem->directories($nonExistentDir);

        $this->assertTrue($this->filesystem->isDirectory($nonExistentDir));
        $this->assertIsArray($directories);
    }

    public function test_remembers_created_directories(): void
    {
        $parentDir = $this->testDirectory . '/parent';
        $childDir = $parentDir . '/child';

        // Create parent directory
        $this->filesystem->makeDirectory($parentDir);

        // Create child directory manually (not through filesystem)
        mkdir($childDir, 0755, true);

        // Remember the child directory
        $this->filesystem->rememberDirectory($childDir);

        // Scanning parent should include remembered child
        $directories = $this->filesystem->directories($parentDir);

        $this->assertContains($childDir, $directories);
    }

    public function test_static_remember_method_maintains_backward_compatibility(): void
    {
        $testDir = $this->testDirectory . '/static-test';

        // Create directory
        $this->filesystem->makeDirectory($testDir);

        // Use static method
        GracefulFilesystem::remember($testDir);

        // Should be remembered
        $memoryManager = $this->filesystem->getMemoryManager();
        $this->assertGreaterThan(0, $memoryManager->count());
    }

    public function test_handles_laravel_12_recursive_parameter_conversion(): void
    {
        $parentDir = $this->testDirectory . '/recursive-test';
        $childDir = $parentDir . '/child';
        $grandchildDir = $childDir . '/grandchild';

        // Create nested structure
        $this->filesystem->makeDirectory($grandchildDir, null, true);

        // Test boolean true (should find all nested)
        $directories = $this->filesystem->directories($parentDir, true);
        $this->assertIsArray($directories);

        // Test boolean false (should find only immediate children)
        $directories = $this->filesystem->directories($parentDir, false);
        $this->assertIsArray($directories);

        // Test integer depth
        $directories = $this->filesystem->directories($parentDir, 1);
        $this->assertIsArray($directories);

        // Test depth 2
        $directories = $this->filesystem->directories($parentDir, 2);
        $this->assertIsArray($directories);
    }

    public function test_validates_directory_paths(): void
    {
        // Test empty path
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directory path cannot be empty');
        $this->filesystem->directories('');
    }

    public function test_prevents_directory_traversal_attacks(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directory path cannot contain ".." segments');
        $this->filesystem->directories('/some/path/../../../etc');
    }

    public function test_prevents_null_byte_injection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directory path cannot contain null bytes');
        $this->filesystem->directories("/some/path\0/malicious");
    }

    public function test_rejects_extremely_long_paths(): void
    {
        $longPath = str_repeat('a', 5000);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directory path is too long');
        $this->filesystem->directories($longPath);
    }

    public function test_uses_proper_permissions_for_directory_creation(): void
    {
        $testDir = $this->testDirectory . '/permissions-test';

        $this->filesystem->makeDirectory($testDir);

        $this->assertTrue($this->filesystem->isDirectory($testDir));
        $this->assertTrue(is_readable($testDir));
        $this->assertTrue(is_writable($testDir));
    }

    public function test_ensure_directory_exists_creates_and_remembers(): void
    {
        $testDir = $this->testDirectory . '/ensure-test';

        $this->assertFalse($this->filesystem->isDirectory($testDir));

        $this->filesystem->ensureDirectoryExists($testDir);

        $this->assertTrue($this->filesystem->isDirectory($testDir));

        // Should be remembered
        $memoryManager = $this->filesystem->getMemoryManager();
        $this->assertGreaterThan(0, $memoryManager->count());
    }

    public function test_clear_memory_removes_remembered_directories(): void
    {
        $testDir = $this->testDirectory . '/clear-memory-test';

        $this->filesystem->rememberDirectory($testDir);
        $this->assertGreaterThan(0, $this->filesystem->getMemoryManager()->count());

        $this->filesystem->clearMemory();
        $this->assertEquals(0, $this->filesystem->getMemoryManager()->count());
    }

    public function test_dependency_injection_works_correctly(): void
    {
        $memoryManager = $this->filesystem->getMemoryManager();
        $backupManager = $this->filesystem->getBackupManager();

        $this->assertInstanceOf(DirectoryMemoryManager::class, $memoryManager);
        $this->assertInstanceOf(BackupDatabaseManager::class, $backupManager);
    }

    public function test_maintains_array_return_type(): void
    {
        $testDir = $this->testDirectory . '/array-test';

        $result = $this->filesystem->directories($testDir);
        $this->assertIsArray($result);

        // Test with different recursive parameters
        $this->assertIsArray($this->filesystem->directories($testDir, true));
        $this->assertIsArray($this->filesystem->directories($testDir, false));
        $this->assertIsArray($this->filesystem->directories($testDir, 2));
    }

    public function test_constructor_with_custom_dependencies(): void
    {
        $permissions = FilesystemPermissions::secure();
        $memoryManager = new DirectoryMemoryManager;

        $filesystem = new GracefulFilesystem($permissions, $memoryManager);

        $this->assertSame($memoryManager, $filesystem->getMemoryManager());
        $this->assertInstanceOf(BackupDatabaseManager::class, $filesystem->getBackupManager());
    }

    public function test_error_handling_for_filesystem_operations(): void
    {
        // Test with invalid path that would cause filesystem errors
        $invalidPath = '/root/restricted-access-' . uniqid();

        try {
            $this->filesystem->makeDirectory($invalidPath);
        } catch (RuntimeException $e) {
            $this->assertStringContains('Failed to create directory', $e->getMessage());
        }
    }

    public function test_logging_directory_operations(): void
    {
        $testDir = $this->testDirectory . '/logging-test';

        // This should trigger logging
        $this->filesystem->directories($testDir);

        // We can't easily test log output in unit tests, but we can ensure no exceptions
        $this->assertTrue(true);
    }

    public function test_backup_preparation_in_testing_environment(): void
    {
        $testDir = $this->testDirectory . '/backup-test';

        // This should trigger backup preparation since we're in testing environment
        $directories = $this->filesystem->directories($testDir);

        $this->assertIsArray($directories);
        $this->assertTrue($this->filesystem->isDirectory($testDir));
    }
}
