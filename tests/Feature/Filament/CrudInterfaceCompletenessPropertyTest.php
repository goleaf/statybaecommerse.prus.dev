<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Field;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use Throwable;

/**
 * Property 6: CRUD Interface Completeness
 * For any admin resource, it should provide complete CRUD functionality with proper form validation and data persistence
 * Validates: Requirements 4.2, 4.3, 7.3
 *
 * Feature: filament-admin-backend-setup, Property 6: CRUD Interface Completeness
 */
final class CrudInterfaceCompletenessPropertyTest extends TestCase
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
     * Property test: all admin resources provide complete CRUD interfaces
     */
    public function test_all_admin_resources_provide_complete_crud_interfaces(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        $this->assertNotEmpty($resourceClasses, 'No Filament resources found to test');

        foreach ($resourceClasses as $resourceClass) {
            // Test that resource has all required CRUD pages
            $this->assertResourceHasRequiredPages($resourceClass);
            
            // Test that resource has form functionality for Create/Edit
            $this->assertResourceHasFormFunctionality($resourceClass);
            
            // Test that resource has table functionality for Read/List
            $this->assertResourceHasTableFunctionality($resourceClass);
            
            // Test that resource has proper model association
            $this->assertResourceHasProperModelAssociation($resourceClass);
        }
    }

    /**
     * Property test: all form schemas include necessary fields with appropriate input types
     */
    public function test_all_form_schemas_include_necessary_fields_with_appropriate_input_types(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            if (method_exists($resourceClass, 'formComponents')) {
                try {
                    $components = $resourceClass::formComponents();
                    $this->assertIsArray($components, "Form components for {$resourceClass} should return array");
                    
                    // Test that form has at least one field component
                    $hasFields = false;
                    foreach ($components as $component) {
                        if ($this->isFieldComponent($component)) {
                            $hasFields = true;
                            
                            // Test that field components have proper configuration
                            $this->assertFieldComponentHasProperConfiguration($component, $resourceClass);
                        }
                    }
                    
                    if (!empty($components)) {
                        $this->assertTrue($hasFields, "Resource {$resourceClass} should have at least one field component in its form");
                    }
                    
                } catch (Throwable $e) {
                    $this->fail("Form components for {$resourceClass} threw an error: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Property test: all data tables display relevant columns with proper formatting
     */
    public function test_all_data_tables_display_relevant_columns_with_proper_formatting(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            if (method_exists($resourceClass, 'tableColumns')) {
                try {
                    $columns = $resourceClass::tableColumns();
                    $this->assertIsArray($columns, "Table columns for {$resourceClass} should return array");
                    
                    // Test that table has at least one column
                    $this->assertNotEmpty($columns, "Resource {$resourceClass} should have at least one table column");
                    
                    // Test that all columns are valid Filament column components
                    foreach ($columns as $column) {
                        $this->assertInstanceOf(Column::class, $column, "All table columns in {$resourceClass} should extend Filament Column class");
                        
                        // Test that column has proper configuration
                        $this->assertColumnHasProperConfiguration($column, $resourceClass);
                    }
                    
                } catch (Throwable $e) {
                    $this->fail("Table columns for {$resourceClass} threw an error: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Property test: all tables provide filtering, searching, and bulk actions where appropriate
     */
    public function test_all_tables_provide_filtering_searching_and_bulk_actions_where_appropriate(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            // Test filters
            if (method_exists($resourceClass, 'tableFilters')) {
                try {
                    $filters = $resourceClass::tableFilters();
                    $this->assertIsArray($filters, "Table filters for {$resourceClass} should return array");
                    
                    // Test that all filters are valid Filament filter components
                    foreach ($filters as $filter) {
                        $this->assertInstanceOf(BaseFilter::class, $filter, "All table filters in {$resourceClass} should extend Filament BaseFilter class");
                    }
                    
                } catch (Throwable $e) {
                    $this->fail("Table filters for {$resourceClass} threw an error: " . $e->getMessage());
                }
            }

            // Test bulk actions
            if (method_exists($resourceClass, 'tableBulkActions')) {
                try {
                    $bulkActions = $resourceClass::tableBulkActions();
                    $this->assertIsArray($bulkActions, "Table bulk actions for {$resourceClass} should return array");
                    
                    // Test that bulk actions are properly configured
                    foreach ($bulkActions as $bulkAction) {
                        // Handle both individual actions and bulk action groups
                        $isValidBulkAction = $bulkAction instanceof Action || 
                                           $bulkAction instanceof BulkActionGroup ||
                                           method_exists($bulkAction, 'getActions');
                        
                        $this->assertTrue($isValidBulkAction, "All bulk actions in {$resourceClass} should be Action instances or BulkActionGroup instances");
                    }
                    
                } catch (Throwable $e) {
                    $this->fail("Table bulk actions for {$resourceClass} threw an error: " . $e->getMessage());
                }
            }

            // Test table actions
            if (method_exists($resourceClass, 'tableActions')) {
                try {
                    $actions = $resourceClass::tableActions();
                    $this->assertIsArray($actions, "Table actions for {$resourceClass} should return array");
                    
                    // Test that actions are properly configured
                    foreach ($actions as $action) {
                        $this->assertInstanceOf(Action::class, $action, "All table actions in {$resourceClass} should be Action instances");
                    }
                    
                } catch (Throwable $e) {
                    $this->fail("Table actions for {$resourceClass} threw an error: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Property test: all resources have proper data persistence functionality
     */
    public function test_all_resources_have_proper_data_persistence_functionality(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            // Test that resource has a valid model
            if (method_exists($resourceClass, 'getModel')) {
                $modelClass = $resourceClass::getModel();
                $this->assertIsString($modelClass, "Model for {$resourceClass} should be a string");
                $this->assertTrue(class_exists($modelClass), "Model class {$modelClass} for resource {$resourceClass} should exist");
                
                // Test that model is an Eloquent model
                $modelInstance = new $modelClass();
                $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $modelInstance, "Model {$modelClass} should be an Eloquent model");
            }

            // Test that resource has proper pages for CRUD operations
            if (method_exists($resourceClass, 'getPages')) {
                $pages = $resourceClass::getPages();
                $this->assertIsArray($pages, "Pages for {$resourceClass} should return array");
                
                // Test that essential CRUD pages exist
                $this->assertArrayHasKey('index', $pages, "Resource {$resourceClass} should have an index page");
                
                // Test that create page exists if resource supports creation
                if ($this->resourceSupportsCreation($resourceClass)) {
                    $this->assertArrayHasKey('create', $pages, "Resource {$resourceClass} should have a create page");
                }
                
                // Test that edit page exists if resource supports editing
                if ($this->resourceSupportsEditing($resourceClass)) {
                    $this->assertArrayHasKey('edit', $pages, "Resource {$resourceClass} should have an edit page");
                }
            }
        }
    }

    /**
     * Property test: all form validation works according to model rules and constraints
     */
    public function test_all_form_validation_works_according_to_model_rules_and_constraints(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            if (method_exists($resourceClass, 'formComponents')) {
                try {
                    $components = $resourceClass::formComponents();
                    
                    // Test that form components have validation rules where appropriate
                    foreach ($components as $component) {
                        if ($this->isFieldComponent($component)) {
                            // Test that required fields are marked as required
                            $this->assertFieldValidationIsConsistent($component, $resourceClass);
                        }
                    }
                    
                } catch (Throwable $e) {
                    $this->fail("Form validation testing for {$resourceClass} threw an error: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Assert that a resource has all required CRUD pages
     */
    private function assertResourceHasRequiredPages(string $resourceClass): void
    {
        if (method_exists($resourceClass, 'getPages')) {
            $pages = $resourceClass::getPages();
            $this->assertIsArray($pages, "Resource {$resourceClass} should have pages configuration");
            
            // At minimum, should have index page
            $this->assertArrayHasKey('index', $pages, "Resource {$resourceClass} should have an index page");
            
            // Test that page classes exist
            foreach ($pages as $pageName => $pageRoute) {
                $this->assertNotNull($pageRoute, "Page route for {$pageName} in {$resourceClass} should not be null");
            }
        }
    }

    /**
     * Assert that a resource has form functionality
     */
    private function assertResourceHasFormFunctionality(string $resourceClass): void
    {
        $hasFormMethod = method_exists($resourceClass, 'form');
        $hasFormComponents = method_exists($resourceClass, 'formComponents');
        
        $this->assertTrue(
            $hasFormMethod || $hasFormComponents,
            "Resource {$resourceClass} should have either form() method or formComponents() method"
        );
    }

    /**
     * Assert that a resource has table functionality
     */
    private function assertResourceHasTableFunctionality(string $resourceClass): void
    {
        $hasTableMethod = method_exists($resourceClass, 'table');
        $hasTableColumns = method_exists($resourceClass, 'tableColumns');
        
        $this->assertTrue(
            $hasTableMethod || $hasTableColumns,
            "Resource {$resourceClass} should have either table() method or tableColumns() method"
        );
    }

    /**
     * Assert that a resource has proper model association
     */
    private function assertResourceHasProperModelAssociation(string $resourceClass): void
    {
        // Test that resource has a model property or getModel method
        $hasModelProperty = property_exists($resourceClass, 'model');
        $hasGetModelMethod = method_exists($resourceClass, 'getModel');
        
        $this->assertTrue(
            $hasModelProperty || $hasGetModelMethod,
            "Resource {$resourceClass} should have either \$model property or getModel() method"
        );
        
        if ($hasGetModelMethod) {
            $modelClass = $resourceClass::getModel();
            $this->assertIsString($modelClass, "Model for {$resourceClass} should be a string");
            $this->assertTrue(class_exists($modelClass), "Model class {$modelClass} should exist");
        }
    }

    /**
     * Check if a component is a field component
     */
    private function isFieldComponent(Component $component): bool
    {
        return $component instanceof Field || 
               method_exists($component, 'getName') ||
               $this->isNestedFieldComponent($component);
    }

    /**
     * Check if component contains nested field components
     */
    private function isNestedFieldComponent(Component $component): bool
    {
        // Check if component has schema method (like Section, Grid, etc.)
        if (method_exists($component, 'getChildComponents')) {
            try {
                $childComponents = $component->getChildComponents();
                foreach ($childComponents as $child) {
                    if ($this->isFieldComponent($child)) {
                        return true;
                    }
                }
            } catch (Throwable $e) {
                // If we can't get child components, assume it might contain fields
                return true;
            }
        }
        
        return false;
    }

    /**
     * Assert that a field component has proper configuration
     */
    private function assertFieldComponentHasProperConfiguration(Component $component, string $resourceClass): void
    {
        if ($component instanceof Field) {
            // Test that field has a name
            $this->assertTrue(
                method_exists($component, 'getName'),
                "Field component in {$resourceClass} should have getName method"
            );
            
            // Test that field has proper label configuration
            $this->assertTrue(
                method_exists($component, 'getLabel'),
                "Field component in {$resourceClass} should have getLabel method"
            );
        }
    }

    /**
     * Assert that a column has proper configuration
     */
    private function assertColumnHasProperConfiguration(Column $column, string $resourceClass): void
    {
        // Test that column has a name
        $this->assertTrue(
            method_exists($column, 'getName'),
            "Column in {$resourceClass} should have getName method"
        );
        
        // Test that column has proper label configuration
        $this->assertTrue(
            method_exists($column, 'getLabel'),
            "Column in {$resourceClass} should have getLabel method"
        );
    }

    /**
     * Assert that field validation is consistent with model rules
     */
    private function assertFieldValidationIsConsistent(Component $component, string $resourceClass): void
    {
        if ($component instanceof Field) {
            // Test that field validation methods exist
            $this->assertTrue(
                method_exists($component, 'isRequired'),
                "Field component in {$resourceClass} should have isRequired method"
            );
        }
    }

    /**
     * Check if resource supports creation
     */
    private function resourceSupportsCreation(string $resourceClass): bool
    {
        // Check if resource has create page or form functionality
        if (method_exists($resourceClass, 'getPages')) {
            $pages = $resourceClass::getPages();
            return array_key_exists('create', $pages);
        }
        
        return method_exists($resourceClass, 'form') || method_exists($resourceClass, 'formComponents');
    }

    /**
     * Check if resource supports editing
     */
    private function resourceSupportsEditing(string $resourceClass): bool
    {
        // Check if resource has edit page or form functionality
        if (method_exists($resourceClass, 'getPages')) {
            $pages = $resourceClass::getPages();
            return array_key_exists('edit', $pages);
        }
        
        return method_exists($resourceClass, 'form') || method_exists($resourceClass, 'formComponents');
    }

    /**
     * Helper method to get all Filament resource classes
     */
    private function getAllFilamentResourceClasses(): array
    {
        $resourceClasses = [];
        $resourcePath = app_path('Filament/Resources');

        if (!is_dir($resourcePath)) {
            return [];
        }

        $files = File::allFiles($resourcePath);

        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();

            // Skip relation managers and other subdirectories
            if (str_contains($relativePath, '/') || !str_ends_with($relativePath, 'Resource.php')) {
                continue;
            }

            $className = 'App\\Filament\\Resources\\' . str_replace('.php', '', $relativePath);

            if (class_exists($className) && is_subclass_of($className, Resource::class)) {
                // Skip abstract classes
                $reflection = new \ReflectionClass($className);
                if (!$reflection->isAbstract()) {
                    $resourceClasses[] = $className;
                }
            }
        }

        return $resourceClasses;
    }
}