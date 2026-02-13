<?php

declare(strict_types=1);

use App\Filament\Resources\ProductResource;
use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = AdminUser::factory()->create();
    $this->actingAs($this->admin, 'admin');

    $this->brand = Brand::factory()->create(['name' => 'Test Brand']);
    $this->category = Category::factory()->create(['name' => 'Test Category']);
    $this->collection = Collection::factory()->create(['name' => 'Test Collection']);

    $this->product = Product::factory()->create([
        'name'           => 'Test Product',
        'slug'           => 'test-product',
        'sku'            => 'TEST-001',
        'price'          => 99.99,
        'cost_price'     => 59.99,
        'stock_quantity' => 100,
        'manage_stock'   => true,
        'is_enabled'     => true,
        'is_featured'    => false,
        'status'         => 'published',
        'published_at'   => now(),
        'brand_id'       => $this->brand->id,
    ]);

    $this->product->categories()->attach($this->category->id);
    $this->product->collections()->attach($this->collection->id);
});

it('can render product resource index page', function () {
    $response = $this->get(ProductResource::getUrl('index'));

    $response->assertOk();
});

it('can list products in table', function () {
    $products = Product::factory()->count(3)->create();

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->assertCanSeeTableRecords($products);
});

it('can render product resource create page', function () {
    $response = $this->get(ProductResource::getUrl('create'));

    $response->assertOk();
});

it('can create product', function () {
    $newProductData = [
        'name'           => 'New Test Product',
        'slug'           => 'new-test-product',
        'sku'            => 'NEW-001',
        'price'          => 149.99,
        'cost_price'     => 89.99,
        'stock_quantity' => 50,
        'manage_stock'   => true,
        'is_featured'    => true,
        'status'         => 'published',
        'published_at'   => now()->toDateTimeString(),
        'brand_id'       => $this->brand->id,
    ];

    Livewire::test(ProductResource\Pages\CreateProduct::class)
        ->fillForm($newProductData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('products', [
        'name' => 'New Test Product',
        'sku'  => 'NEW-001',
    ]);

    // Check price separately due to decimal handling
    $createdProduct = Product::where('sku', 'NEW-001')->first();
    expect((float) $createdProduct->price)->toBe(149.99);
});

it('validates required fields when creating product', function () {
    Livewire::test(ProductResource\Pages\CreateProduct::class)
        ->fillForm([
            'name'  => '',
            'sku'   => '',
            'price' => '',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name'  => 'required',
            'sku'   => 'required',
            'price' => 'required',
        ]);
});

it('validates unique slug when creating product', function () {
    Livewire::test(ProductResource\Pages\CreateProduct::class)
        ->fillForm([
            'name'  => 'Another Product',
            'slug'  => $this->product->slug, // Use existing slug
            'sku'   => 'ANOTHER-SKU-001',
            'price' => 99.99,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('does not register a dedicated product view page', function () {
    $pages = ProductResource::getPages();

    expect($pages)->not->toHaveKey('view');
});

it('can inspect product details through edit page', function () {
    Livewire::test(ProductResource\Pages\EditProduct::class, [
        'record' => $this->product->getRouteKey(),
    ])
        ->assertFormSet([
            'sku'   => $this->product->sku,
            'price' => (float) $this->product->price,
        ]);
});

it('can render product resource edit page', function () {
    $response = $this->get(ProductResource::getUrl('edit', ['record' => $this->product]));

    $response->assertOk();
});

it('can retrieve product data for editing', function () {
    Livewire::test(ProductResource\Pages\EditProduct::class, [
        'record' => $this->product->getRouteKey(),
    ])
        ->assertFormSet([
            'sku'            => $this->product->sku,
            'price'          => (float) $this->product->price,
            'cost_price'     => (float) $this->product->cost_price,
            'stock_quantity' => $this->product->stock_quantity,
            'manage_stock'   => $this->product->manage_stock,
            'is_featured'    => $this->product->is_featured,
            'status'         => $this->product->status,
            'brand_id'       => $this->product->brand_id,
        ]);
});

it('can save product', function () {
    $updatedData = [
        'name'           => 'Updated Product Name',
        'price'          => 199.99,
        'stock_quantity' => 75,
        'is_featured'    => true,
    ];

    Livewire::test(ProductResource\Pages\EditProduct::class, [
        'record' => $this->product->getRouteKey(),
    ])
        ->fillForm($updatedData)
        ->call('save')
        ->assertHasNoFormErrors();

    $freshProduct = $this->product->fresh();
    expect($freshProduct)
        ->name->toBe('Updated Product Name')
        ->stock_quantity->toBe(75)
        ->is_featured->toBeTrue();

    // Handle decimal comparison for price
    expect((float) $freshProduct->price)->toBe(199.99);
});

it('can delete product', function () {
    Livewire::test(ProductResource\Pages\EditProduct::class, [
        'record' => $this->product->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($this->product);
});

it('can filter products by brand', function () {
    $anotherBrand = Brand::factory()->create(['name' => 'Another Brand']);
    $productWithAnotherBrand = Product::factory()->create(['brand_id' => $anotherBrand->id]);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->filterTable('brand', $this->brand->id)
        ->assertCanSeeTableRecords([$this->product])
        ->assertCanNotSeeTableRecords([$productWithAnotherBrand]);
});

it('can filter products by status', function () {
    Product::factory()->create(['status' => 'draft']);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->filterTable('status', 'published')
        ->assertCanSeeTableRecords([$this->product]);
});

it('can filter products by enabled state', function () {
    $disabledProduct = Product::factory()->create(['is_enabled' => false]);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->filterTable('is_enabled', true)
        ->assertCanSeeTableRecords([$this->product])
        ->assertCanNotSeeTableRecords([$disabledProduct]);
});

it('can search products by name', function () {
    $anotherProduct = Product::factory()->create(['name' => 'Different Product']);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->searchTable('Test Product')
        ->assertCanSeeTableRecords([$this->product])
        ->assertCanNotSeeTableRecords([$anotherProduct]);
});

it('can search products by sku', function () {
    $anotherProduct = Product::factory()->create(['sku' => 'DIFFERENT-001']);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->searchTable('TEST-001')
        ->assertCanSeeTableRecords([$this->product])
        ->assertCanNotSeeTableRecords([$anotherProduct]);
});

it('can sort products by price', function () {
    $cheapProduct = Product::factory()->create(['price' => 49.99]);
    $expensiveProduct = Product::factory()->create(['price' => 199.99]);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->sortTable('price')
        ->assertCanSeeTableRecords([$cheapProduct, $this->product, $expensiveProduct], inOrder: true);
});

it('can bulk publish products', function () {
    $draftProducts = Product::factory()->count(3)->create(['status' => 'draft', 'is_enabled' => false]);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->callTableBulkAction('publish', $draftProducts)
        ->assertNotified();

    foreach ($draftProducts as $product) {
        expect($product->fresh())
            ->status->toBe('published')
            ->is_enabled->toBeTrue();
    }
});

it('can bulk unpublish products', function () {
    $publishedProducts = Product::factory()->count(3)->create(['status' => 'published', 'is_enabled' => true]);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->callTableBulkAction('unpublish', $publishedProducts)
        ->assertNotified();

    foreach ($publishedProducts as $product) {
        expect($product->fresh())
            ->status->toBe('draft')
            ->published_at->toBeNull();
    }
});

it('can bulk feature products', function () {
    $products = Product::factory()->count(3)->create(['is_featured' => false]);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->callTableBulkAction('feature', $products)
        ->assertNotified();

    foreach ($products as $product) {
        expect($product->fresh()->is_featured)->toBeTrue();
    }
});

it('can bulk update stock', function () {
    $products = Product::factory()->count(3)->create(['stock_quantity' => 10]);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->callTableBulkAction('update_stock', $products, [
            'stock_quantity'      => 50,
            'low_stock_threshold' => 10,
        ])
        ->assertNotified();
});

it('can bulk update prices with percentage increase', function () {
    $products = Product::factory()->count(3)->create(['price' => 100.00]);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->callTableBulkAction('update_prices', $products, [
            'price_increase_percentage' => 10,
        ])
        ->assertNotified();
});

it('displays correct table columns', function () {
    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->assertTableColumnExists('main_image')
        ->assertTableColumnExists('name')
        ->assertTableColumnExists('price')
        ->assertTableColumnExists('sales_sparkline')
        ->assertTableColumnExists('cost_price')
        ->assertTableColumnExists('weight')
        ->assertTableColumnExists('status')
        ->assertTableColumnExists('created_at');
});

it('has correct form fields in create page', function () {
    Livewire::test(ProductResource\Pages\CreateProduct::class)
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('slug')
        ->assertFormFieldExists('description')
        ->assertFormFieldExists('short_description')
        ->assertFormFieldExists('sku')
        ->assertFormFieldExists('barcode')
        ->assertFormFieldExists('price')
        ->assertFormFieldExists('cost_price')
        ->assertFormFieldExists('manage_stock')
        ->assertFormFieldExists('stock_quantity')
        ->assertFormFieldExists('low_stock_threshold')
        ->assertFormFieldExists('brand_id')
        ->assertFormFieldExists('status')
        ->assertFormFieldExists('is_featured')
        ->assertFormFieldExists('allow_backorder')
        ->assertFormFieldExists('published_at')
        ->assertFormFieldExists('weight')
        ->assertFormFieldExists('length')
        ->assertFormFieldExists('width')
        ->assertFormFieldExists('height');
});

it('does not expose categories field directly in product form', function () {
    Livewire::test(ProductResource\Pages\CreateProduct::class)
        ->assertFormFieldDoesNotExist('categories');
});

it('can access table actions', function () {
    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->assertTableActionExists(EditAction::class)
        ->assertTableActionExists(DeleteAction::class);
});

it('validates price fields are numeric', function () {
    Livewire::test(ProductResource\Pages\CreateProduct::class)
        ->fillForm([
            'name'  => 'Test Product',
            'sku'   => 'TEST-002',
            'price' => 'not-a-number',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'price' => 'numeric',
        ]);
});

it('validates stock quantity is integer', function () {
    Livewire::test(ProductResource\Pages\CreateProduct::class)
        ->fillForm([
            'name'           => 'Test Product',
            'sku'            => 'TEST-003',
            'price'          => 99.99,
            'stock_quantity' => 'not-a-number',
        ])
        ->call('create')
        ->assertHasFormErrors(['stock_quantity']);
});

it('can handle product with variants', function () {
    $productWithVariants = Product::factory()->create();
    ProductVariant::factory()->count(3)->create([
        'product_id' => $productWithVariants->id,
    ]);

    Livewire::test(ProductResource\Pages\EditProduct::class, [
        'record' => $productWithVariants->getRouteKey(),
    ])
        ->assertSuccessful();
});

it('can handle product with categories and collections', function () {
    $categories = Category::factory()->count(2)->create();
    $collections = Collection::factory()->count(2)->create();

    $product = Product::factory()->create();
    $product->categories()->attach($categories);
    $product->collections()->attach($collections);

    Livewire::test(ProductResource\Pages\EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->assertSuccessful();
});
