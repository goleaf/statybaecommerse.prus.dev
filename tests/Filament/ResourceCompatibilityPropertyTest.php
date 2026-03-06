<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->adminUser = User::factory()->create([
        'email'    => 'info@egisstatyba.lt',
        'is_admin' => true,
    ]);
});

/**
 * Property 1: Resource Compatibility Consistency
 * For any Filament admin resource in the system, accessing that resource should load without fatal errors and use correct Filament 4 syntax and imports
 * Validates: Requirements 1.2, 1.4, 4.1
 *
 * Feature: filament-admin-backend-setup, Property 1: Resource Compatibility Consistency
 */
describe('Resource Compatibility Property Tests', function (): void {
    it('property: all resources load without fatal errors', function (): void {
        // Get all Filament resource classes
        $resourceClasses = $this->getAllFilamentResourceClasses();

        expect($resourceClasses)->not->toBeEmpty('No Filament resources found to test');

        foreach ($resourceClasses as $resourceClass) {
            // Test that each resource can be instantiated and accessed without fatal errors
            expect(class_exists($resourceClass))
                ->toBeTrue("Resource class {$resourceClass} should exist");

            expect(is_subclass_of($resourceClass, Resource::class))
                ->toBeTrue("Resource class {$resourceClass} should extend Filament Resource");

            // Test that the resource has the correct method signatures for Filament 4
            $this->assertResourceHasCorrectSignatures($resourceClass);
        }
    });

    it('property: all resources use correct Filament 4 form method signature', function (): void {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            if (method_exists($resourceClass, 'form')) {
                $reflection = new ReflectionMethod($resourceClass, 'form');
                $parameters = $reflection->getParameters();

                expect($parameters)->toHaveCount(1, "Resource {$resourceClass}::form() should have exactly one parameter");

                $parameter = $parameters[0];
                $parameterType = $parameter->getType();

                // In Filament 4, the parameter should be Schema, not Form
                if ($parameterType && $parameterType instanceof ReflectionNamedType) {
                    $typeName = $parameterType->getName();
                    expect($typeName)
                        ->toBe('Filament\Schemas\Schema', "Resource {$resourceClass}::form() should use Schema parameter, not Form");
                }
            }
        }
    });

    it('property: all resources can be registered in admin panel without errors', function (): void {
        $panel = Filament::getPanel('admin');
        $resourceClasses = $this->getAllFilamentResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            // Test that the resource can be registered without throwing exceptions
            try {
                // Check if resource should be registered
                if (method_exists($resourceClass, 'shouldRegisterNavigation')) {
                    $shouldRegister = $resourceClass::shouldRegisterNavigation();

                    if ($shouldRegister) {
                        // Test navigation methods don't throw errors
                        if (method_exists($resourceClass, 'getNavigationLabel')) {
                            $label = $resourceClass::getNavigationLabel();
                            expect($label)->toBeString("Navigation label for {$resourceClass} should be a string");
                        }

                        if (method_exists($resourceClass, 'getNavigationIcon')) {
                            $icon = $resourceClass::getNavigationIcon();
                            expect($icon)->toBeString("Navigation icon for {$resourceClass} should be a string or null");
                        }
                    }
                }

                // Test model methods
                if (method_exists($resourceClass, 'getModel')) {
                    $model = $resourceClass::getModel();
                    expect($model)->toBeString("Model for {$resourceClass} should be a string");
                    expect(class_exists($model))->toBeTrue("Model class {$model} for resource {$resourceClass} should exist");
                }

            } catch (Throwable $e) {
                $this->fail("Resource {$resourceClass} threw an error during registration: " . $e->getMessage());
            }
        }
    });

    it('property: all resources have correct import statements for Filament 4', function (): void {
        $resourceFiles = $this->getAllFilamentResourceFiles();

        foreach ($resourceFiles as $file) {
            $content = File::get($file);

            // Check for old Filament 3 imports that should be updated
            $oldImports = [
                'use Filament\Forms\Form;',
            ];

            foreach ($oldImports as $oldImport) {
                expect($content)
                    ->not->toContain($oldImport, "File {$file} should not contain old import: {$oldImport}");
            }

            // If the file has a form method, it should import Schema
            if (str_contains($content, 'public static function form(')) {
                expect($content)
                    ->toContain('use Filament\Schemas\Schema;', "File {$file} with form method should import Schema");
            }
        }
    });
});

/**
 * Helper method to get all Filament resource classes
 */
function getAllFilamentResourceClasses(): array
{
    $resourceClasses = [];
    $resourcePath = app_path('Filament/Resources');

    if (! is_dir($resourcePath)) {
        return [];
    }

    $files = File::allFiles($resourcePath);

    foreach ($files as $file) {
        $relativePath = $file->getRelativePathname();

        // Skip relation managers and other subdirectories
        if (str_contains($relativePath, '/') || ! str_ends_with($relativePath, 'Resource.php')) {
            continue;
        }

        $className = 'App\\Filament\\Resources\\' . str_replace('.php', '', $relativePath);

        if (class_exists($className) && is_subclass_of($className, Resource::class)) {
            $resourceClasses[] = $className;
        }
    }

    return $resourceClasses;
}

/**
 * Helper method to get all Filament resource files
 */
function getAllFilamentResourceFiles(): array
{
    $resourcePath = app_path('Filament/Resources');

    if (! is_dir($resourcePath)) {
        return [];
    }

    $files = File::allFiles($resourcePath);
    $resourceFiles = [];

    foreach ($files as $file) {
        if (str_ends_with($file->getFilename(), 'Resource.php')) {
            $resourceFiles[] = $file->getPathname();
        }
    }

    return $resourceFiles;
}

/**
 * Helper method to assert resource has correct method signatures
 */
function assertResourceHasCorrectSignatures(string $resourceClass): void
{
    // Check form method signature if it exists
    if (method_exists($resourceClass, 'form')) {
        $reflection = new ReflectionMethod($resourceClass, 'form');

        expect($reflection->isStatic())
            ->toBeTrue("Resource {$resourceClass}::form() should be static");

        expect($reflection->isPublic())
            ->toBeTrue("Resource {$resourceClass}::form() should be public");
    }

    // Check table method signature if it exists
    if (method_exists($resourceClass, 'table')) {
        $reflection = new ReflectionMethod($resourceClass, 'table');

        expect($reflection->isStatic())
            ->toBeTrue("Resource {$resourceClass}::table() should be static");

        expect($reflection->isPublic())
            ->toBeTrue("Resource {$resourceClass}::table() should be public");
    }
}
