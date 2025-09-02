<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole($adminRole);
        $this->actingAs($this->adminUser);
    }

    public function test_can_render_product_index_page(): void
    {
        $this->get(ProductResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_list_products(): void
    {
        $products = Product::factory()->count(10)->create();

        Livewire::test(ProductResource\Pages\ListProducts::class)
            ->assertCanSeeTableRecords($products);
    }

    public function test_can_render_product_create_page(): void
    {
        $this->get(ProductResource::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_can_create_product(): void
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $newData = [
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-123',
            'price' => 99.99,
            'brand_id' => $brand->id,
            'status' => 'published',
            'type' => 'simple',
            'is_visible' => true,
            'categories' => [$category->id],
        ];

        Livewire::test(ProductResource\Pages\CreateProduct::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST-123',
            'brand_id' => $brand->id,
        ]);
    }

    public function test_can_validate_product_creation(): void
    {
        Livewire::test(ProductResource\Pages\CreateProduct::class)
            ->fillForm([
                'name' => '',
                'sku' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'sku' => 'required']);
    }

    public function test_can_render_product_edit_page(): void
    {
        $product = Product::factory()->create();

        $this->get(ProductResource::getUrl('edit', ['record' => $product]))
            ->assertSuccessful();
    }

    public function test_can_retrieve_product_data_for_editing(): void
    {
        $product = Product::factory()->create();

        Livewire::test(ProductResource\Pages\EditProduct::class, [
            'record' => $product->getRouteKey(),
        ])
            ->assertFormSet([
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
            ]);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create();

        $newData = [
            'name' => 'Updated Product Name',
            'price' => 199.99,
        ];

        Livewire::test(ProductResource\Pages\EditProduct::class, [
            'record' => $product->getRouteKey(),
        ])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'price' => 199.99,
        ]);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        Livewire::test(ProductResource\Pages\EditProduct::class, [
            'record' => $product->getRouteKey(),
        ])
            ->callAction('delete');

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_can_view_product(): void
    {
        $product = Product::factory()->create();

        $this->get(ProductResource::getUrl('view', ['record' => $product]))
            ->assertSuccessful();
    }

    public function test_can_search_products(): void
    {
        $productA = Product::factory()->create(['name' => 'Awesome Product']);
        $productB = Product::factory()->create(['name' => 'Different Item']);

        Livewire::test(ProductResource\Pages\ListProducts::class)
            ->searchTable('Awesome')
            ->assertCanSeeTableRecords([$productA])
            ->assertCanNotSeeTableRecords([$productB]);
    }

    public function test_can_filter_products_by_brand(): void
    {
        $brandA = Brand::factory()->create(['name' => 'Brand A']);
        $brandB = Brand::factory()->create(['name' => 'Brand B']);
        
        $productA = Product::factory()->create(['brand_id' => $brandA->id]);
        $productB = Product::factory()->create(['brand_id' => $brandB->id]);

        Livewire::test(ProductResource\Pages\ListProducts::class)
            ->filterTable('brand', $brandA->id)
            ->assertCanSeeTableRecords([$productA])
            ->assertCanNotSeeTableRecords([$productB]);
    }

    public function test_can_sort_products(): void
    {
        $products = Product::factory()->count(3)->create();

        Livewire::test(ProductResource\Pages\ListProducts::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords($products, inOrder: true);
    }
}
