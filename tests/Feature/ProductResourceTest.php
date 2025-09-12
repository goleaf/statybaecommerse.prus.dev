<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_can_list_products(): void
    {
        $products = Product::factory()->count(3)->create();

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->assertCanSeeTableRecords($products);
    }

    public function test_can_create_product(): void
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $collection = Collection::factory()->create();

        $newProduct = Livewire::test(\App\Filament\Resources\ProductResource\Pages\CreateProduct::class)
            ->fillForm([
                'name' => 'New Product',
                'slug' => 'new-product',
                'sku' => 'NEW-001',
                'price' => 99.99,
                'brand_id' => $brand->id,
                'type' => 'simple',
                'status' => 'draft',
                'is_visible' => true,
                'manage_stock' => true,
                'stock_quantity' => 10,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'slug' => 'new-product',
            'sku' => 'NEW-001',
            'price' => 99.99,
            'brand_id' => $brand->id,
        ]);
    }

    public function test_can_edit_product(): void
    {
        $product = Product::factory()->create(['name' => 'Original Name']);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\EditProduct::class, [
            'record' => $product->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Name',
                'price' => 149.99,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();
        $this->assertEquals('Updated Name', $product->name);
        $this->assertEquals(149.99, $product->price);
    }

    public function test_can_view_product(): void
    {
        $product = Product::factory()->create();

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ViewProduct::class, [
            'record' => $product->getRouteKey(),
        ])
            ->assertFormSet([
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
            ]);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->callTableAction('delete', $product);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_can_duplicate_product(): void
    {
        $product = Product::factory()->create(['name' => 'Original Product']);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->callTableAction('duplicate', $product);

        $this->assertDatabaseHas('products', [
            'name' => 'Original Product (Copy)',
            'sku' => $product->sku . '-copy',
            'slug' => $product->slug . '-copy',
        ]);
    }

    public function test_can_bulk_publish_products(): void
    {
        $products = Product::factory()->count(3)->create(['status' => 'draft']);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->callTableBulkAction('publish', $products);

        foreach ($products as $product) {
            $product->refresh();
            $this->assertEquals('published', $product->status);
            $this->assertTrue($product->is_visible);
            $this->assertNotNull($product->published_at);
        }
    }

    public function test_can_bulk_unpublish_products(): void
    {
        $products = Product::factory()->count(3)->create([
            'status' => 'published',
            'is_visible' => true,
        ]);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->callTableBulkAction('unpublish', $products);

        foreach ($products as $product) {
            $product->refresh();
            $this->assertEquals('draft', $product->status);
            $this->assertFalse($product->is_visible);
        }
    }

    public function test_can_bulk_feature_products(): void
    {
        $products = Product::factory()->count(3)->create(['is_featured' => false]);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->callTableBulkAction('feature', $products);

        foreach ($products as $product) {
            $product->refresh();
            $this->assertTrue($product->is_featured);
        }
    }

    public function test_can_bulk_unfeature_products(): void
    {
        $products = Product::factory()->count(3)->create(['is_featured' => true]);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->callTableBulkAction('unfeature', $products);

        foreach ($products as $product) {
            $product->refresh();
            $this->assertFalse($product->is_featured);
        }
    }

    public function test_can_filter_products_by_status(): void
    {
        $publishedProduct = Product::factory()->create(['status' => 'published']);
        $draftProduct = Product::factory()->create(['status' => 'draft']);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->filterTable('status', 'published')
            ->assertCanSeeTableRecords([$publishedProduct])
            ->assertCanNotSeeTableRecords([$draftProduct]);
    }

    public function test_can_filter_products_by_brand(): void
    {
        $brand = Brand::factory()->create();
        $productWithBrand = Product::factory()->create(['brand_id' => $brand->id]);
        $productWithoutBrand = Product::factory()->create(['brand_id' => null]);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->filterTable('brand_id', $brand->id)
            ->assertCanSeeTableRecords([$productWithBrand])
            ->assertCanNotSeeTableRecords([$productWithoutBrand]);
    }

    public function test_can_filter_products_by_type(): void
    {
        $simpleProduct = Product::factory()->create(['type' => 'simple']);
        $variableProduct = Product::factory()->create(['type' => 'variable']);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->filterTable('type', 'simple')
            ->assertCanSeeTableRecords([$simpleProduct])
            ->assertCanNotSeeTableRecords([$variableProduct]);
    }

    public function test_can_filter_products_by_visibility(): void
    {
        $visibleProduct = Product::factory()->create(['is_visible' => true]);
        $hiddenProduct = Product::factory()->create(['is_visible' => false]);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->filterTable('is_visible', true)
            ->assertCanSeeTableRecords([$visibleProduct])
            ->assertCanNotSeeTableRecords([$hiddenProduct]);
    }

    public function test_can_filter_products_by_featured(): void
    {
        $featuredProduct = Product::factory()->create(['is_featured' => true]);
        $regularProduct = Product::factory()->create(['is_featured' => false]);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->filterTable('is_featured', true)
            ->assertCanSeeTableRecords([$featuredProduct])
            ->assertCanNotSeeTableRecords([$regularProduct]);
    }

    public function test_can_filter_products_by_stock_management(): void
    {
        $managedProduct = Product::factory()->create(['manage_stock' => true]);
        $unmanagedProduct = Product::factory()->create(['manage_stock' => false]);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->filterTable('manage_stock', true)
            ->assertCanSeeTableRecords([$managedProduct])
            ->assertCanNotSeeTableRecords([$unmanagedProduct]);
    }

    public function test_can_filter_products_by_low_stock(): void
    {
        $lowStockProduct = Product::factory()->create([
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
        ]);
        $normalStockProduct = Product::factory()->create([
            'stock_quantity' => 20,
            'low_stock_threshold' => 10,
        ]);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->filterTable('low_stock', true)
            ->assertCanSeeTableRecords([$lowStockProduct])
            ->assertCanNotSeeTableRecords([$normalStockProduct]);
    }

    public function test_can_filter_products_by_out_of_stock(): void
    {
        $outOfStockProduct = Product::factory()->create(['stock_quantity' => 0]);
        $inStockProduct = Product::factory()->create(['stock_quantity' => 10]);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->filterTable('out_of_stock', true)
            ->assertCanSeeTableRecords([$outOfStockProduct])
            ->assertCanNotSeeTableRecords([$inStockProduct]);
    }

    public function test_can_filter_products_by_published(): void
    {
        $publishedProduct = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        $draftProduct = Product::factory()->create([
            'is_visible' => false,
            'status' => 'draft',
        ]);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->filterTable('published', true)
            ->assertCanSeeTableRecords([$publishedProduct])
            ->assertCanNotSeeTableRecords([$draftProduct]);
    }

    public function test_can_search_products(): void
    {
        $product1 = Product::factory()->create(['name' => 'Test Product 1']);
        $product2 = Product::factory()->create(['name' => 'Another Product']);
        $product3 = Product::factory()->create(['sku' => 'TEST-001']);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->searchTable('Test')
            ->assertCanSeeTableRecords([$product1, $product3])
            ->assertCanNotSeeTableRecords([$product2]);
    }

    public function test_can_sort_products_by_name(): void
    {
        $product1 = Product::factory()->create(['name' => 'Z Product']);
        $product2 = Product::factory()->create(['name' => 'A Product']);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords([$product2, $product1], inOrder: true);
    }

    public function test_can_sort_products_by_price(): void
    {
        $product1 = Product::factory()->create(['price' => 100.00]);
        $product2 = Product::factory()->create(['price' => 50.00]);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->sortTable('price')
            ->assertCanSeeTableRecords([$product2, $product1], inOrder: true);
    }

    public function test_can_sort_products_by_created_at(): void
    {
        $product1 = Product::factory()->create(['created_at' => now()->subDay()]);
        $product2 = Product::factory()->create(['created_at' => now()]);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class)
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$product2, $product1], inOrder: true);
    }

    public function test_can_use_tabs_to_filter_products(): void
    {
        $publishedProduct = Product::factory()->create([
            'status' => 'published',
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);
        $draftProduct = Product::factory()->create(['status' => 'draft']);
        $featuredProduct = Product::factory()->create(['is_featured' => true]);

        $component = Livewire::test(\App\Filament\Resources\ProductResource\Pages\ListProducts::class);

        $component->assertCanSeeTableRecords([$publishedProduct, $draftProduct, $featuredProduct]);

        $component->tab('published')
            ->assertCanSeeTableRecords([$publishedProduct])
            ->assertCanNotSeeTableRecords([$draftProduct, $featuredProduct]);

        $component->tab('draft')
            ->assertCanSeeTableRecords([$draftProduct])
            ->assertCanNotSeeTableRecords([$publishedProduct, $featuredProduct]);

        $component->tab('featured')
            ->assertCanSeeTableRecords([$featuredProduct])
            ->assertCanNotSeeTableRecords([$publishedProduct, $draftProduct]);
    }

    public function test_form_validation_works(): void
    {
        Livewire::test(\App\Filament\Resources\ProductResource\Pages\CreateProduct::class)
            ->fillForm([
                'name' => '', // Required field
                'sku' => '', // Required field
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'sku']);
    }

    public function test_sku_must_be_unique(): void
    {
        $existingProduct = Product::factory()->create(['sku' => 'EXISTING-001']);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\CreateProduct::class)
            ->fillForm([
                'name' => 'New Product',
                'sku' => 'EXISTING-001', // Duplicate SKU
            ])
            ->call('create')
            ->assertHasFormErrors(['sku']);
    }

    public function test_slug_must_be_unique(): void
    {
        $existingProduct = Product::factory()->create(['slug' => 'existing-product']);

        Livewire::test(\App\Filament\Resources\ProductResource\Pages\CreateProduct::class)
            ->fillForm([
                'name' => 'New Product',
                'slug' => 'existing-product', // Duplicate slug
                'sku' => 'NEW-001',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_can_toggle_stock_management_fields(): void
    {
        Livewire::test(\App\Filament\Resources\ProductResource\Pages\CreateProduct::class)
            ->fillForm([
                'name' => 'Test Product',
                'sku' => 'TEST-001',
                'manage_stock' => false,
            ])
            ->assertFormFieldIsHidden('track_stock')
            ->assertFormFieldIsHidden('stock_quantity')
            ->assertFormFieldIsHidden('low_stock_threshold')
            ->fillForm(['manage_stock' => true])
            ->assertFormFieldIsVisible('track_stock')
            ->assertFormFieldIsVisible('stock_quantity')
            ->assertFormFieldIsVisible('low_stock_threshold');
    }
}


