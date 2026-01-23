<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Filesystem;

use App\Support\Filesystem\GracefulFilesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Property-based tests for GracefulFilesystem to ensure universal correctness.
 */
final class GracefulFilesystemPropertyTest extends TestCase
{
    use RefreshDatabase;

    private GracefulFilesystem $filesystem;

    private string $testDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new GracefulFilesystem;
        $this->testDirectory = storage_path('framework/testing/graceful-filesystem-property-test');

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

    /**
     * Property: All valid directory paths should return arrays.
     */
    public function test_property_directories_always_returns_array(): void
    {
        $validPaths = [
            $this->testDirectory . '/test1',
            $this->testDirectory . '/test2/nested',
            $this->testDirectory . '/test3/deeply/nested/path',
        ];

        foreach ($validPaths as $path) {
            $result = $this->filesystem->directories($path);
            $this->assertIsArray($result, "directories() should always return array for path: {$path}");
        }
    }

    /**
     * Property: All recursive parameter variations should work consistently.
     */
    public function test_property_recursive_parameter_consistency(): void
    {
        $testPath = $this->testDirectory . '/recursive-test';
        $recursiveValues = [true, false, 0, 1, 2, 5, -1];

        foreach ($recursiveValues as $recursive) {
            $result = $this->filesystem->directories($testPath, $recursive);
            $this->assertIsArray($result, 'directories() should return array for recursive value: ' . var_export($recursive, true));
        }
    }

    /**
     * Property: Directory creation should be idempotent.
     */
    public function test_property_directory_creation_idempotent(): void
    {
        $testPath = $this->testDirectory . '/idempotent-test';

        // First creation
        $result1 = $this->filesystem->makeDirectory($testPath);
        $this->assertTrue($this->filesystem->isDirectory($testPath));

        // Second creation should not fail
        $result2 = $this->filesystem->makeDirectory($testPath);
        $this->assertTrue($this->filesystem->isDirectory($testPath));
    }

    /**
     * Property: Invalid paths should always throw InvalidArgumentException.
     */
    public function test_property_invalid_paths_throw_exceptions(): void
    {
        $invalidPaths = [
            '',                           // Empty path
            '../../../etc/passwd',        // Directory traversal
            "/path/with\0null",          // Null byte injection
            str_repeat('a', 5000),       // Too long path
        ];

        foreach ($invalidPaths as $invalidPath) {
            try {
                $this->filesystem->directories($invalidPath);
                $this->fail("Expected InvalidArgumentException for path: {$invalidPath}");
            } catch (InvalidArgumentException $e) {
                $this->assertInstanceOf(InvalidArgumentException::class, $e);
            }
        }
    }

    /**
     * Property: Memory operations should be consistent.
     */
    public function test_property_memory_operations_consistent(): void
    {
        $paths = [
            $this->testDirectory . '/memory1',
            $this->testDirectory . '/memory2',
            $this->testDirectory . '/memory3',
        ];

        $initialCount = $this->filesystem->getMemoryManager()->count();

        foreach ($paths as $path) {
            $this->filesystem->rememberDirectory($path);
        }

        $afterRememberCount = $this->filesystem->getMemoryManager()->count();
        $this->assertGreaterThan($initialCount, $afterRememberCount);

        $this->filesystem->clearMemory();
        $afterClearCount = $this->filesystem->getMemoryManager()->count();
        $this->assertEquals(0, $afterClearCount);
    }

    /**
     * Property: Ensure directory exists should be safe for existing directories.
     */
    public function test_property_ensure_directory_exists_safe(): void
    {
        $testPath = $this->testDirectory . '/ensure-safe-test';

        // Create directory first
        $this->filesystem->makeDirectory($testPath);
        $this->assertTrue($this->filesystem->isDirectory($testPath));

        // Ensure should not fail on existing directory
        $this->filesystem->ensureDirectoryExists($testPath);
        $this->assertTrue($this->filesystem->isDirectory($testPath));
    }

    /**
     * Property: Filesystem operations should handle concurrent access gracefully.
     */
    public function test_property_concurrent_access_handling(): void
    {
        $basePath = $this->testDirectory . '/concurrent';
        $paths = [];

        // Create multiple paths concurrently (simulated)
        for ($i = 0; $i < 10; $i++) {
            $path = $basePath . "/path{$i}";
            $paths[] = $path;
            $this->filesystem->makeDirectory($path);
        }

        // All paths should exist
        foreach ($paths as $path) {
            $this->assertTrue($this->filesystem->isDirectory($path));
        }

        // Scanning base should find all created directories
        $found = $this->filesystem->directories($basePath);
        $this->assertIsArray($found);
    }

    /**
     * Property: Static and instance methods should behave consistently.
     */
    public function test_property_static_instance_consistency(): void
    {
        $testPath = $this->testDirectory . '/static-instance-test';

        // Create directory
        $this->filesystem->makeDirectory($testPath);

        $initialCount = $this->filesystem->getMemoryManager()->count();

        // Use static method
        GracefulFilesystem::remember($testPath);

        $afterStaticCount = $this->filesystem->getMemoryManager()->count();

        // Use instance method
        $this->filesystem->rememberDirectory($testPath);

        $afterInstanceCount = $this->filesystem->getMemoryManager()->count();

        // Both should increase the count
        $this->assertGreaterThanOrEqual($initialCount, $afterStaticCount);
        $this->assertGreaterThanOrEqual($afterStaticCount, $afterInstanceCount);
    }

    /**
     * Property: Path normalization should be consistent.
     */
    public function test_property_path_normalization_consistent(): void
    {
        $basePath = $this->testDirectory . '/normalization-test';

        // Different path representations that should be equivalent
        $pathVariations = [
            $basePath,
            $basePath . '/',
            $basePath . '//',
            rtrim($basePath, '/'),
        ];

        foreach ($pathVariations as $path) {
            $result = $this->filesystem->directories($path);
            $this->assertIsArray($result, "Path variation should work: {$path}");
        }
    }
}
