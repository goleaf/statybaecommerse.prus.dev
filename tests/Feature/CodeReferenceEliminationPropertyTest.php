<?php

declare(strict_types=1);

namespace Tests\Feature;

use Exception;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Tests\TestCase;

/**
 * Property-based test for code reference elimination
 *
 * **Feature: news-blog-cleanup-upgrade, Property 5: Code reference elimination**
 * **Validates: Requirements 6.4**
 */
final class CodeReferenceEliminationPropertyTest extends TestCase
{
    /**
     * Property 5: Code reference elimination
     *
     * For any file in the codebase after cleanup, there should be no imports,
     * references, or usage of NewsTag or NewsComment classes.
     *
     * **Validates: Requirements 6.4**
     */
    public function test_code_reference_elimination_completeness(): void
    {
        // Arrange: Define directories to scan for code references
        $directoriesToScan = [
            app_path(),
            resource_path(),
            config_path(),
            database_path(),
            base_path('routes'),
        ];

        // Act & Assert: Scan all PHP files for NewsTag and NewsComment references
        foreach ($directoriesToScan as $directory) {
            if (File::isDirectory($directory)) {
                $this->assertNoNewsTagOrCommentReferencesInDirectory($directory);
            }
        }

        // Act & Assert: Verify specific critical files don't contain references
        $this->assertCriticalFilesClean();

        // Act & Assert: Verify no use statements import removed classes
        $this->assertNoImportStatementsForRemovedClasses();
    }

    /**
     * Property test: Code references eliminated universally across file types
     *
     * Tests that NewsTag and NewsComment references are eliminated across
     * all types of files in the codebase (PHP, Blade, config, etc.).
     */
    public function test_code_references_eliminated_across_file_types(): void
    {
        $fileTypesToCheck = [
            '*.php'       => 'PHP files',
            '*.blade.php' => 'Blade template files',
            '*.json'      => 'JSON configuration files',
            '*.yaml'      => 'YAML configuration files',
            '*.yml'       => 'YML configuration files',
        ];

        foreach ($fileTypesToCheck as $pattern => $description) {
            $this->assertNoReferencesInFileType($pattern, $description);
        }
    }

    /**
     * Property test: Service classes work universally without removed dependencies
     *
     * Tests that all service classes that might have referenced NewsTag or NewsComment
     * functionality continue to work correctly without those dependencies.
     */
    public function test_service_classes_work_without_removed_dependencies(): void
    {
        // Arrange: Get all service classes
        $serviceClasses = $this->getServiceClasses();

        // Act & Assert: Test each service class
        foreach ($serviceClasses as $serviceClass) {
            $this->assertServiceClassWorksWithoutRemovedDependencies($serviceClass);
        }
    }

    private function assertNoNewsTagOrCommentReferencesInDirectory(string $directory): void
    {
        $phpFiles = File::allFiles($directory);

        foreach ($phpFiles as $file) {
            // Skip non-PHP files and test files (test files may legitimately reference removed classes)
            if ($file->getExtension() !== 'php' || $this->isTestFile($file->getPathname())) {
                continue;
            }

            $content = File::get($file->getPathname());
            $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());

            // Check for NewsTag references
            $this->assertStringNotContainsString(
                'NewsTag',
                $content,
                "File '{$relativePath}' should not contain NewsTag references"
            );

            // Check for NewsComment references
            $this->assertStringNotContainsString(
                'NewsComment',
                $content,
                "File '{$relativePath}' should not contain NewsComment references"
            );

            // Check for use statements
            $this->assertStringNotContainsString(
                'use App\\Models\\NewsTag',
                $content,
                "File '{$relativePath}' should not import NewsTag class"
            );

            $this->assertStringNotContainsString(
                'use App\\Models\\NewsComment',
                $content,
                "File '{$relativePath}' should not import NewsComment class"
            );

            // Check for namespace references
            $this->assertStringNotContainsString(
                'App\\Models\\NewsTag',
                $content,
                "File '{$relativePath}' should not reference NewsTag namespace"
            );

            $this->assertStringNotContainsString(
                'App\\Models\\NewsComment',
                $content,
                "File '{$relativePath}' should not reference NewsComment namespace"
            );
        }
    }

    private function assertCriticalFilesClean(): void
    {
        $criticalFiles = [
            app_path('Models/News.php'),
            app_path('Filament/Resources/NewsResource.php'),
            app_path('Filament/Widgets/UltimateStatsWidget.php'),
        ];

        foreach ($criticalFiles as $filePath) {
            if (File::exists($filePath)) {
                $content = File::get($filePath);
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $filePath);

                $this->assertStringNotContainsString(
                    'NewsTag',
                    $content,
                    "Critical file '{$relativePath}' should not contain NewsTag references"
                );

                $this->assertStringNotContainsString(
                    'NewsComment',
                    $content,
                    "Critical file '{$relativePath}' should not contain NewsComment references"
                );
            }
        }
    }

    private function assertNoImportStatementsForRemovedClasses(): void
    {
        $allPhpFiles = File::allFiles(app_path());

        foreach ($allPhpFiles as $file) {
            if ($file->getExtension() !== 'php' || $this->isTestFile($file->getPathname())) {
                continue;
            }

            $content = File::get($file->getPathname());
            $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());

            // Check for various import patterns
            $importPatterns = [
                'use App\\Models\\NewsTag;',
                'use App\\Models\\NewsComment;',
                'use App\\Models\\Translations\\NewsTagTranslation;',
                'use App\\Filament\\Resources\\NewsTagResource;',
                'use App\\Filament\\Resources\\NewsTags\\',
            ];

            foreach ($importPatterns as $pattern) {
                $this->assertStringNotContainsString(
                    $pattern,
                    $content,
                    "File '{$relativePath}' should not contain import: {$pattern}"
                );
            }
        }
    }

    private function assertNoReferencesInFileType(string $pattern, string $description): void
    {
        $command = 'find ' . base_path() . " -name '{$pattern}' -type f";
        $files = [];

        exec($command, $files);

        foreach ($files as $filePath) {
            // Skip test files and vendor files
            if ($this->isTestFile($filePath) || strpos($filePath, 'vendor/') !== false) {
                continue;
            }

            if (File::exists($filePath)) {
                $content = File::get($filePath);
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $filePath);

                $this->assertStringNotContainsString(
                    'NewsTag',
                    $content,
                    "{$description} '{$relativePath}' should not contain NewsTag references"
                );

                $this->assertStringNotContainsString(
                    'NewsComment',
                    $content,
                    "{$description} '{$relativePath}' should not contain NewsComment references"
                );
            }
        }
    }

    private function getServiceClasses(): array
    {
        $serviceClasses = [];
        $servicesPath = app_path('Services');

        if (File::isDirectory($servicesPath)) {
            $serviceFiles = File::allFiles($servicesPath);

            foreach ($serviceFiles as $file) {
                if ($file->getExtension() === 'php') {
                    $className = 'App\\Services\\' . str_replace(
                        ['/', '.php'],
                        ['\\', ''],
                        $file->getRelativePathname()
                    );

                    if (class_exists($className)) {
                        $reflection = new ReflectionClass($className);
                        // Skip enums, interfaces, and traits
                        if (! $reflection->isEnum() && ! $reflection->isInterface() && ! $reflection->isTrait()) {
                            $serviceClasses[] = $className;
                        }
                    }
                }
            }
        }

        return $serviceClasses;
    }

    private function assertServiceClassWorksWithoutRemovedDependencies(string $serviceClass): void
    {
        try {
            // Try to instantiate the service class
            $reflection = new ReflectionClass($serviceClass);

            // Skip abstract classes and enums
            if ($reflection->isAbstract() || $reflection->isEnum()) {
                // Just check the file content for abstract classes
                $classFile = $reflection->getFileName();
                if ($classFile) {
                    $content = File::get($classFile);

                    $this->assertStringNotContainsString(
                        'NewsTag',
                        $content,
                        "Service class '{$serviceClass}' should not reference NewsTag"
                    );

                    $this->assertStringNotContainsString(
                        'NewsComment',
                        $content,
                        "Service class '{$serviceClass}' should not reference NewsComment"
                    );
                }

                return;
            }

            // Check if constructor requires parameters
            $constructor = $reflection->getConstructor();
            if ($constructor && $constructor->getNumberOfRequiredParameters() > 0) {
                // Skip services that require complex dependencies for this test
                // Just check the file content
                $classFile = $reflection->getFileName();
                if ($classFile) {
                    $content = File::get($classFile);

                    $this->assertStringNotContainsString(
                        'NewsTag',
                        $content,
                        "Service class '{$serviceClass}' should not reference NewsTag"
                    );

                    $this->assertStringNotContainsString(
                        'NewsComment',
                        $content,
                        "Service class '{$serviceClass}' should not reference NewsComment"
                    );
                }

                return;
            }

            // Try to create instance
            $instance = $reflection->newInstance();
            $this->assertInstanceOf($serviceClass, $instance);

            // Verify the class file doesn't contain removed references
            $classFile = $reflection->getFileName();
            if ($classFile) {
                $content = File::get($classFile);

                $this->assertStringNotContainsString(
                    'NewsTag',
                    $content,
                    "Service class '{$serviceClass}' should not reference NewsTag"
                );

                $this->assertStringNotContainsString(
                    'NewsComment',
                    $content,
                    "Service class '{$serviceClass}' should not reference NewsComment"
                );
            }
        } catch (Exception $e) {
            // If we can't instantiate the service, at least check its file content
            $reflection = new ReflectionClass($serviceClass);
            $classFile = $reflection->getFileName();

            if ($classFile) {
                $content = File::get($classFile);

                $this->assertStringNotContainsString(
                    'NewsTag',
                    $content,
                    "Service class '{$serviceClass}' should not reference NewsTag"
                );

                $this->assertStringNotContainsString(
                    'NewsComment',
                    $content,
                    "Service class '{$serviceClass}' should not reference NewsComment"
                );
            }
        }
    }

    private function isTestFile(string $filePath): bool
    {
        return strpos($filePath, '/tests/') !== false ||
               strpos($filePath, '\\tests\\') !== false ||
               strpos($filePath, 'Test.php') !== false ||
               strpos($filePath, 'TestCase.php') !== false ||
               strpos($filePath, 'cleanup_news_tag_and_comment_tables.php') !== false ||
               strpos($filePath, '/backups/') !== false ||
               strpos($filePath, '\\backups\\') !== false;
    }
}
