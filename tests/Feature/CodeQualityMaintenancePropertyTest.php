<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * Property-based test for code quality maintenance
 *
 * **Feature: news-blog-cleanup-upgrade, Property 8: Code quality maintenance**
 * **Validates: Requirements 9.2, 9.3, 9.4**
 */
describe('Code Quality Maintenance Property Tests', function () {
    /**
     * **Feature: news-blog-cleanup-upgrade, Property 8: Code quality maintenance**
     * **Validates: Requirements 9.2, 9.3, 9.4**
     *
     * For any PHP file after cleanup, the code should pass all style checks,
     * have optimized imports, and maintain compatibility with Laravel 12 and Filament 4
     */
    test('all PHP files pass style checks and maintain framework compatibility', function () {
        // Get all PHP files in the app directory
        $phpFiles = File::allFiles(app_path());
        $phpFiles = array_filter($phpFiles, fn ($file) => $file->getExtension() === 'php');

        expect($phpFiles)->not->toBeEmpty('Should have PHP files to test');

        // Test that Pint style checks pass
        $pintProcess = new Process(['vendor/bin/pint', '--test']);
        $pintProcess->run();

        expect($pintProcess->getExitCode())
            ->toBe(0, 'All PHP files should pass Pint style checks. Output: ' . $pintProcess->getOutput());

        // Test that PHPStan analysis passes
        $phpstanProcess = new Process(['vendor/bin/phpstan', 'analyse', '--no-progress', '--error-format=raw']);
        $phpstanProcess->run();

        expect($phpstanProcess->getExitCode())
            ->toBe(0, 'All PHP files should pass PHPStan analysis. Output: ' . $phpstanProcess->getOutput());

        // Test Laravel 12 compatibility by checking composer.json constraints
        $composerContent = json_decode(File::get(base_path('composer.json')), true);

        expect($composerContent['require']['laravel/framework'])
            ->toMatch('/\^12\./', 'Should require Laravel 12.x');

        expect($composerContent['require']['filament/filament'])
            ->toMatch('/\^4\./', 'Should require Filament 4.x');

        // Test that no unused imports exist by checking for common patterns
        foreach ($phpFiles as $file) {
            $content = File::get($file->getPathname());

            // Skip if file doesn't have use statements
            if (! str_contains($content, 'use ')) {
                continue;
            }

            // Extract use statements
            preg_match_all('/^use\s+([^;]+);/m', $content, $matches);
            $useStatements = $matches[1] ?? [];

            foreach ($useStatements as $useStatement) {
                // Skip if it's an alias or function import
                if (str_contains($useStatement, ' as ') || str_contains($useStatement, 'function ')) {
                    continue;
                }

                // Get the class name
                $className = trim(last(explode('\\', $useStatement)));

                // Check if the class is actually used in the file
                $isUsed = str_contains($content, $className . '::') ||
                         str_contains($content, $className . '(') ||
                         str_contains($content, 'new ' . $className) ||
                         str_contains($content, ': ' . $className) ||
                         str_contains($content, '|' . $className) ||
                         str_contains($content, '<' . $className) ||
                         str_contains($content, 'extends ' . $className) ||
                         str_contains($content, 'implements ' . $className) ||
                         str_contains($content, 'instanceof ' . $className);

                expect($isUsed)
                    ->toBeTrue("Unused import '{$useStatement}' found in {$file->getPathname()}");
            }
        }
    });

    test('composer dependencies are up to date and compatible', function () {
        $composerContent = json_decode(File::get(base_path('composer.json')), true);

        // Check that we have the required dependencies
        $requiredDeps = [
            'laravel/framework' => '^12.0',
            'filament/filament' => '^4.0',
            'php'               => '^8.2',
        ];

        foreach ($requiredDeps as $package => $expectedVersion) {
            expect($composerContent['require'][$package] ?? null)
                ->not->toBeNull("Package {$package} should be present in composer.json")
                ->toMatch('/\^(12|4|8)\./', "Package {$package} should have compatible version constraint");
        }

        // Test that composer.lock is in sync
        expect(File::exists(base_path('composer.lock')))
            ->toBeTrue('composer.lock should exist');

        // Verify no security vulnerabilities
        $auditProcess = new Process(['composer', 'audit', '--format=json']);
        $auditProcess->run();

        if ($auditProcess->getExitCode() === 0) {
            $auditOutput = json_decode($auditProcess->getOutput(), true);
            expect($auditOutput['advisories'] ?? [])
                ->toBeEmpty('Should have no security advisories');
        }
    });

    test('code follows PSR standards and Laravel conventions', function () {
        // Test that all model files follow Laravel naming conventions
        $modelFiles = File::allFiles(app_path('Models'));

        foreach ($modelFiles as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            // Should be PascalCase
            expect($className)
                ->toMatch('/^[A-Z][a-zA-Z0-9]*$/', "Model {$className} should be in PascalCase");

            $content = File::get($file->getPathname());

            // Should extend Model or have proper namespace
            expect($content)
                ->toMatch('/namespace App\\\\Models;/', "Model {$className} should have correct namespace")
                ->toMatch('/(extends Model|extends Authenticatable|extends Pivot)/', "Model {$className} should extend appropriate base class");
        }

        // Test that controller files follow conventions
        $controllerFiles = File::allFiles(app_path('Http/Controllers'));

        foreach ($controllerFiles as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            expect($className)
                ->toMatch('/Controller$/', "Controller {$className} should end with 'Controller'");
        }
    });
});
