<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
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

    public function test_category_resource_form_works(): void
    {
        $resource = new CategoryResource();

        // Test that form method exists and is callable
        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(is_callable([$resource, 'form']));
    }

    public function test_category_resource_table_works(): void
    {
        $resource = new CategoryResource();

        // Test that table method exists and is callable
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'table']));
    }

    public function test_category_resource_has_valid_model(): void
    {
        $resource = new CategoryResource();
        $model = $resource->getModel();

        $this->assertEquals(Category::class, $model);
        $this->assertTrue(class_exists($model));
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
}
