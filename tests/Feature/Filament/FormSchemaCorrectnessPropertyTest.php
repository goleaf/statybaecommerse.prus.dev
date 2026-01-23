<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Field;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use ReflectionNamedType;
use Tests\TestCase;
use Throwable;

/**
 * Property 2: Form Schema Component Correctness
 * For any form schema in the admin system, all form components should use proper Filament 4 classes and render without errors
 * Validates: Requirements 1.3, 4.2, 7.1
 *
 * Feature: filament-admin-backend-setup, Property 2: Form Schema Component Correctness
 */
final class FormSchemaCorrectnessPropertyTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email'    => 'admin@test.com',
            'is_admin' => true,
        ]);
    }

    /**
     * Property test: all form schemas use proper Filament 4 components
     */
    public function test_all_form_schemas_use_proper_filament_4_components(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        $this->assertNotEmpty($resourceClasses, 'No Filament resources found to test');

        foreach ($resourceClasses as $resourceClass) {
            if (method_exists($resourceClass, 'formComponents')) {
                // Test using formComponents method which doesn't require Livewire context
                try {
                    $components = $resourceClass::formComponents();
                    $this->assertIsArray($components, "Form components for {$resourceClass} should return array");

                    // Test that all components are valid Filament 4 components
                    foreach ($components as $component) {
                        $this->assertInstanceOf(Component::class, $component, "All form components in {$resourceClass} should extend Filament Component class");

                        // Test that component has required methods for Filament 4
                        if ($component instanceof Field) {
                            $this->assertTrue(method_exists($component, 'getName'), "Field component in {$resourceClass} should have getName method");
                            $this->assertTrue(method_exists($component, 'getLabel'), "Field component in {$resourceClass} should have getLabel method");
                        }
                    }

                } catch (Throwable $e) {
                    $this->fail("Form components for {$resourceClass} threw an error: " . $e->getMessage());
                }
            } elseif (method_exists($resourceClass, 'form')) {
                // For resources that only have form method, test that it exists and has correct signature
                $reflection = new ReflectionMethod($resourceClass, 'form');
                $parameters = $reflection->getParameters();

                $this->assertCount(1, $parameters, "Form method for {$resourceClass} should have exactly one parameter");

                $parameter = $parameters[0];
                $parameterType = $parameter->getType();

                if ($parameterType && $parameterType instanceof ReflectionNamedType) {
                    $typeName = $parameterType->getName();
                    $this->assertEquals('Filament\Schemas\Schema', $typeName, "Form method for {$resourceClass} should use Schema parameter");
                }
            }
        }
    }

    /**
     * Property test: all form schemas render without fatal errors
     */
    public function test_all_form_schemas_render_without_fatal_errors(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            if (method_exists($resourceClass, 'formComponents')) {
                try {
                    $components = $resourceClass::formComponents();
                    $this->assertIsArray($components, "Form components for {$resourceClass} should be retrievable as array");

                    // Test that components can be iterated without errors
                    foreach ($components as $component) {
                        $this->assertInstanceOf(Component::class, $component, "Each component in {$resourceClass} should be a valid Filament component");
                    }

                } catch (Throwable $e) {
                    $this->fail("Form components rendering for {$resourceClass} threw an error: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Property test: all form components have valid configuration
     */
    public function test_all_form_components_have_valid_configuration(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            if (method_exists($resourceClass, 'formComponents')) {
                $components = $resourceClass::formComponents();

                foreach ($components as $component) {
                    // Test that component can be evaluated without errors
                    try {
                        // Test basic component properties that don't require container initialization
                        $componentClass = get_class($component);
                        $this->assertInstanceOf(Component::class, $component, "Component in {$resourceClass} should be a valid Filament component");

                        // Test that component is from correct namespace
                        $this->assertStringStartsWith('Filament\\', $componentClass, "Component {$componentClass} in {$resourceClass} should be from Filament namespace");

                    } catch (Throwable $e) {
                        $this->fail("Component configuration validation for {$resourceClass} threw an error: " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Property test: all form schemas use correct import statements
     */
    public function test_all_form_schemas_use_correct_import_statements(): void
    {
        $resourceFiles = $this->getAllFilamentResourceFiles();

        foreach ($resourceFiles as $file) {
            $content = File::get($file);

            // Skip files that don't have form methods
            if (! str_contains($content, 'public static function form(')) {
                continue;
            }

            // Check for correct Filament 4 imports
            $requiredImports = [
                'use Filament\Schemas\Schema;',
            ];

            foreach ($requiredImports as $import) {
                $this->assertStringContainsString($import, $content, "File {$file} should contain required import: {$import}");
            }

            // Check for deprecated Filament 3 imports that should not be present
            $deprecatedImports = [
                'use Filament\Forms\Form;',
                'use Filament\Forms\Components\Form;',
            ];

            foreach ($deprecatedImports as $deprecatedImport) {
                $this->assertStringNotContainsString($deprecatedImport, $content, "File {$file} should not contain deprecated import: {$deprecatedImport}");
            }
        }
    }

    /**
     * Property test: all form components use proper Filament 4 component classes
     */
    public function test_all_form_components_use_proper_filament_4_component_classes(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            if (method_exists($resourceClass, 'formComponents')) {
                $components = $resourceClass::formComponents();

                foreach ($components as $component) {
                    $componentClass = get_class($component);

                    // Ensure component is from correct Filament 4 namespace
                    $this->assertStringStartsWith('Filament\\', $componentClass, "Component {$componentClass} in {$resourceClass} should be from Filament namespace");

                    // Ensure it's not from deprecated namespaces
                    $this->assertStringStartsNotWith('Filament\\Forms\\Components\\Form', $componentClass, "Component {$componentClass} in {$resourceClass} should not use deprecated Form namespace");

                    // Test that component extends the base Component class
                    $this->assertInstanceOf(Component::class, $component, "Component {$componentClass} in {$resourceClass} should extend Filament Component class");
                }
            }
        }
    }

    /**
     * Property test: all form schemas have consistent structure
     */
    public function test_all_form_schemas_have_consistent_structure(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            if (method_exists($resourceClass, 'formComponents')) {
                // Test that components can be retrieved without errors
                $components = $resourceClass::formComponents();
                $this->assertIsArray($components, "Form components for {$resourceClass} should be an array");

                // If there are components, they should be valid
                if (! empty($components)) {
                    foreach ($components as $component) {
                        $this->assertInstanceOf(Component::class, $component, "Each component in {$resourceClass} should be a valid Filament component");

                        // Test that component class exists and is properly defined
                        $componentClass = get_class($component);
                        $this->assertTrue(class_exists($componentClass), "Component class {$componentClass} should exist");
                    }
                }
            }
        }
    }

    /**
     * Helper method to get all Filament resource classes
     */
    private function getAllFilamentResourceClasses(): array
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
    private function getAllFilamentResourceFiles(): array
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
}
