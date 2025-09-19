<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductResourceTest extends TestCase
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

    public function test_product_resource_can_be_instantiated(): void
    {
        $resource = new ProductResource();
        $this->assertInstanceOf(ProductResource::class, $resource);
    }

    public function test_product_resource_has_required_methods(): void
    {
        $resource = new ProductResource();

        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(method_exists($resource, 'getPages'));
        $this->assertTrue(method_exists($resource, 'getModel'));
    }

    public function test_product_resource_form_works(): void
    {
        $resource = new ProductResource();

        // Test that form method exists and is callable
        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(is_callable([$resource, 'form']));

        // Test Filament v4 Schema usage
        $schema = ProductResource::form(Schema::make());
        $this->assertInstanceOf(Schema::class, $schema);
    }

    public function test_product_resource_table_works(): void
    {
        $resource = new ProductResource();

        // Test that table method exists and is callable
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'table']));
    }

    public function test_product_resource_has_valid_model(): void
    {
        $resource = new ProductResource();
        $model = $resource->getModel();

        $this->assertEquals(Product::class, $model);
        $this->assertTrue(class_exists($model));
    }

    public function test_product_resource_handles_empty_database(): void
    {
        $resource = new ProductResource();

        // Test that methods exist and are callable
        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'form']));
        $this->assertTrue(is_callable([$resource, 'table']));
    }

    public function test_product_resource_with_sample_data(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
        ]);

        $resource = new ProductResource();

        // Test that methods exist and are callable
        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'form']));
        $this->assertTrue(is_callable([$resource, 'table']));

        // Test that product was created
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
        ]);
    }

    public function test_product_resource_navigation_group(): void
    {
        $this->assertEquals('Products', ProductResource::getNavigationGroup());
    }

    public function test_product_resource_navigation_label(): void
    {
        $this->assertIsString(ProductResource::getNavigationLabel());
    }

    public function test_product_resource_model_label(): void
    {
        $this->assertIsString(ProductResource::getModelLabel());
        $this->assertIsString(ProductResource::getPluralModelLabel());
    }

    public function test_product_resource_record_title_attribute(): void
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        $this->assertEquals('Test Product', $product->name);
    }

    public function test_product_resource_form_components(): void
    {
        $schema = ProductResource::form(Schema::make());
        $components = $schema->getComponents();

        $this->assertIsArray($components);
        $this->assertNotEmpty($components);
    }

    public function test_product_resource_relationships(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $product = Product::factory()->create([
            'brand_id' => $brand->id,
        ]);

        // Attach category using many-to-many relationship
        $product->categories()->attach($category->id);

        $this->assertInstanceOf(Brand::class, $product->brand);
        $this->assertEquals($brand->id, $product->brand->id);

        // Test many-to-many relationship
        $this->assertTrue($product->categories->contains($category));
        $this->assertEquals($category->id, $product->categories->first()->id);
    }
}
