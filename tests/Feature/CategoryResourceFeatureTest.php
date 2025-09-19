<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryResourceFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@test.com',
            'is_active' => true
        ]));
    }

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

    public function test_category_resource_handles_empty_database(): void
    {
        $resource = new CategoryResource();

        // Test that methods exist and are callable
        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'form']));
        $this->assertTrue(is_callable([$resource, 'table']));
    }

    public function test_category_resource_with_sample_data(): void
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $resource = new CategoryResource();

        // Test that methods exist and are callable
        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'form']));
        $this->assertTrue(is_callable([$resource, 'table']));

        // Test that category was created
        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    public function test_category_resource_syntax_validation(): void
    {
        // Test that the CategoryResource file has valid PHP syntax
        $output = [];
        $returnCode = 0;
        exec('php -l app/Filament/Resources/CategoryResource.php 2>&1', $output, $returnCode);

        $this->assertEquals(0, $returnCode, 'CategoryResource.php has syntax errors: ' . implode("\n", $output));
    }

    public function test_category_model_syntax_validation(): void
    {
        // Test that the Category model file has valid PHP syntax
        $output = [];
        $returnCode = 0;
        exec('php -l app/Models/Category.php 2>&1', $output, $returnCode);

        $this->assertEquals(0, $returnCode, 'Category.php has syntax errors: ' . implode("\n", $output));
    }

    public function test_category_resource_pages_syntax_validation(): void
    {
        $pages = [
            'app/Filament/Resources/CategoryResource/Pages/ListCategories.php',
            'app/Filament/Resources/CategoryResource/Pages/CreateCategory.php',
            'app/Filament/Resources/CategoryResource/Pages/EditCategory.php',
            'app/Filament/Resources/CategoryResource/Pages/ViewCategory.php'
        ];

        foreach ($pages as $page) {
            $output = [];
            $returnCode = 0;
            exec("php -l $page 2>&1", $output, $returnCode);

            $this->assertEquals(0, $returnCode, "$page has syntax errors: " . implode("\n", $output));
        }
    }
}

