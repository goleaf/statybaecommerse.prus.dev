<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use Tests\TestCase;

class CategoryResourceIsolatedTest extends TestCase
{
    public function test_category_resource_can_be_instantiated(): void
    {
        $resource = new CategoryResource();
        $this->assertInstanceOf(CategoryResource::class, $resource);
    }

    public function test_category_resource_has_required_methods(): void
    {
        $resource = new CategoryResource();

        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(method_exists($resource, 'getPages'));
        $this->assertTrue(method_exists($resource, 'getModel'));
    }

    public function test_category_resource_has_valid_model(): void
    {
        $resource = new CategoryResource();
        $model = $resource->getModel();

        $this->assertEquals(Category::class, $model);
        $this->assertTrue(class_exists($model));
    }

    public function test_category_resource_form_method_exists(): void
    {
        $resource = new CategoryResource();

        // Test that form method exists and is callable
        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(is_callable([$resource, 'form']));
    }

    public function test_category_resource_table_method_exists(): void
    {
        $resource = new CategoryResource();

        // Test that table method exists and is callable
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'table']));
    }

    public function test_category_resource_get_pages_method(): void
    {
        $resource = new CategoryResource();

        $this->assertTrue(method_exists($resource, 'getPages'));
        $this->assertTrue(is_callable([$resource, 'getPages']));
    }

    public function test_category_resource_get_relations_method(): void
    {
        $resource = new CategoryResource();

        $this->assertTrue(method_exists($resource, 'getRelations'));
        $this->assertTrue(is_callable([$resource, 'getRelations']));
    }

    public function test_category_resource_navigation_group(): void
    {
        $this->assertEquals(NavigationGroup::Products, CategoryResource::getNavigationGroup());
    }

    public function test_category_resource_navigation_sort(): void
    {
        $this->assertEquals(3, CategoryResource::getNavigationSort());
    }

    public function test_category_resource_record_title_attribute(): void
    {
        $this->assertEquals('name', CategoryResource::getRecordTitleAttribute());
    }

    public function test_category_resource_model_label(): void
    {
        $this->assertIsString(CategoryResource::getModelLabel());
    }

    public function test_category_resource_plural_model_label(): void
    {
        $this->assertIsString(CategoryResource::getPluralModelLabel());
    }

    public function test_category_resource_navigation_label(): void
    {
        $this->assertIsString(CategoryResource::getNavigationLabel());
    }

    public function test_category_resource_syntax_is_valid(): void
    {
        // Test that the CategoryResource file has valid PHP syntax
        $output = [];
        $returnCode = 0;
        exec('php -l app/Filament/Resources/CategoryResource.php 2>&1', $output, $returnCode);

        $this->assertEquals(0, $returnCode, 'CategoryResource.php has syntax errors: ' . implode("\n", $output));
    }

    public function test_category_model_syntax_is_valid(): void
    {
        // Test that the Category model file has valid PHP syntax
        $output = [];
        $returnCode = 0;
        exec('php -l app/Models/Category.php 2>&1', $output, $returnCode);

        $this->assertEquals(0, $returnCode, 'Category.php has syntax errors: ' . implode("\n", $output));
    }
}

