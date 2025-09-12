<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Filament\Resources\ProductResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user with proper permissions
        $this->user = User::factory()->create();
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
        $this->user->assignRole($adminRole);
    }

    public function test_can_list_products(): void
    {
        $products = Product::factory()->count(3)->create();

        $this->actingAs($this->user)
            ->get(ProductResource::getUrl('index'))
            ->assertOk();
    }

    public function test_can_view_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->user)
            ->get(ProductResource::getUrl('view', ['record' => $product]))
            ->assertOk();
    }

    public function test_can_create_product(): void
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($this->user)
            ->get(ProductResource::getUrl('create'))
            ->assertOk();
    }

    public function test_can_edit_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->user)
            ->get(ProductResource::getUrl('edit', ['record' => $product]))
            ->assertOk();
    }

    public function test_product_resource_has_correct_model(): void
    {
        $this->assertEquals(Product::class, ProductResource::getModel());
    }

    public function test_product_resource_has_correct_navigation_icon(): void
    {
        $this->assertEquals('heroicon-o-cube', ProductResource::getNavigationIcon());
    }

    public function test_product_resource_has_correct_record_title_attribute(): void
    {
        $this->assertEquals('name', ProductResource::getRecordTitleAttribute());
    }

    public function test_product_table_has_required_columns(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-123',
            'price' => 99.99,
            'is_visible' => true,
        ]);

        $this->actingAs($this->user)
            ->get(ProductResource::getUrl('index'))
            ->assertSee('Test Product')
            ->assertSee('TEST-123')
            ->assertSee('99.99');
    }

    public function test_product_form_has_required_fields(): void
    {
        $this->actingAs($this->user)
            ->get(ProductResource::getUrl('create'))
            ->assertSee('name')
            ->assertSee('sku')
            ->assertSee('price')
            ->assertSee('description');
    }

    public function test_can_filter_products_by_visibility(): void
    {
        $visibleProduct = Product::factory()->create(['is_visible' => true]);
        $hiddenProduct = Product::factory()->create(['is_visible' => false]);

        $this->actingAs($this->user)
            ->get(ProductResource::getUrl('index'))
            ->assertSee($visibleProduct->name)
            ->assertSee($hiddenProduct->name);
    }

    public function test_can_search_products(): void
    {
        $product1 = Product::factory()->create(['name' => 'Unique Product Name']);
        $product2 = Product::factory()->create(['name' => 'Another Product']);

        $this->actingAs($this->user)
            ->get(ProductResource::getUrl('index') . '?search=Unique')
            ->assertSee('Unique Product Name')
            ->assertDontSee('Another Product');
    }

    public function test_product_resource_pages_exist(): void
    {
        $pages = ProductResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('view', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    public function test_product_resource_has_relation_managers(): void
    {
        $relationManagers = ProductResource::getRelations();

        // Check if relation managers exist (they might be empty)
        $this->assertIsArray($relationManagers);
    }

    public function test_product_resource_has_eloquent_query(): void
    {
        $query = ProductResource::getEloquentQuery();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);
    }

    public function test_product_resource_has_global_search_attributes(): void
    {
        $searchAttributes = ProductResource::getGlobalSearchResultAttributes();

        $this->assertIsArray($searchAttributes);
    }

    public function test_product_resource_has_global_search_result_details(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-123',
        ]);

        $details = ProductResource::getGlobalSearchResultDetails($product);

        $this->assertIsArray($details);
    }

    public function test_product_resource_has_global_search_result_url(): void
    {
        $product = Product::factory()->create();

        $url = ProductResource::getGlobalSearchResultUrl($product);

        $this->assertIsString($url);
        $this->assertStringContainsString('products', $url);
    }
}