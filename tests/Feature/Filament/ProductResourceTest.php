<?php declare(strict_types=1);

use App\Filament\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('can render product resource index page', function () {
    $this
        ->get(ProductResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list products', function () {
    $products = Product::factory()->count(10)->create();

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->assertCanSeeTableRecords($products);
});

it('can create product', function () {
    $brand = Brand::factory()->create();
    $categories = Category::factory()->count(2)->create();

    $newData = [
        'name' => 'Test Product',
        'slug' => 'test-product',
        'price' => 99.99,
        'sku' => 'TEST-001',
        'stock_quantity' => 100,
        'brand_id' => $brand->id,
        'categories' => $categories->pluck('id')->toArray(),
        'status' => 'active',
        'is_visible' => true,
    ];

    Livewire::test(ProductResource\Pages\CreateProduct::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'slug' => 'test-product',
        'price' => 99.99,
        'sku' => 'TEST-001',
        'brand_id' => $brand->id,
        'status' => 'active',
    ]);
});

it('can validate product creation', function () {
    Livewire::test(ProductResource\Pages\CreateProduct::class)
        ->fillForm([
            'name' => '',
            'price' => 'invalid-price',
            'sku' => '',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'price' => 'required',
            'sku' => 'required',
        ]);
});

it('can edit product', function () {
    $product = Product::factory()->create();
    $brand = Brand::factory()->create();

    $newData = [
        'name' => 'Updated Product',
        'price' => 149.99,
        'brand_id' => $brand->id,
        'status' => 'inactive',
    ];

    Livewire::test(ProductResource\Pages\EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->refresh())
        ->name
        ->toBe('Updated Product')
        ->price
        ->toBe(149.99)
        ->brand_id
        ->toBe($brand->id)
        ->status
        ->toBe('inactive');
});

it('can delete product', function () {
    $product = Product::factory()->create();

    Livewire::test(ProductResource\Pages\EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->callAction('delete');

    $this->assertSoftDeleted($product);
});

it('can filter products by status', function () {
    $activeProduct = Product::factory()->create(['status' => 'active']);
    $draftProduct = Product::factory()->create(['status' => 'draft']);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->filterTable('status', 'active')
        ->assertCanSeeTableRecords([$activeProduct])
        ->assertCanNotSeeTableRecords([$draftProduct]);
});

it('can filter products by brand', function () {
    $brand1 = Brand::factory()->create();
    $brand2 = Brand::factory()->create();

    $product1 = Product::factory()->create(['brand_id' => $brand1->id]);
    $product2 = Product::factory()->create(['brand_id' => $brand2->id]);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->filterTable('brand_id', $brand1->id)
        ->assertCanSeeTableRecords([$product1])
        ->assertCanNotSeeTableRecords([$product2]);
});

it('can filter low stock products', function () {
    $lowStockProduct = Product::factory()->create(['stock_quantity' => 5]);
    $normalStockProduct = Product::factory()->create(['stock_quantity' => 50]);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->filterTable('low_stock')
        ->assertCanSeeTableRecords([$lowStockProduct])
        ->assertCanNotSeeTableRecords([$normalStockProduct]);
});

it('can search products by name and sku', function () {
    $product1 = Product::factory()->create([
        'name' => 'iPhone 15',
        'sku' => 'IPH-15-001',
    ]);

    $product2 = Product::factory()->create([
        'name' => 'Samsung Galaxy',
        'sku' => 'SAM-GAL-001',
    ]);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->searchTable('iPhone')
        ->assertCanSeeTableRecords([$product1])
        ->assertCanNotSeeTableRecords([$product2]);
});

it('can view product details', function () {
    $product = Product::factory()->create();

    $this
        ->get(ProductResource::getUrl('view', ['record' => $product]))
        ->assertSuccessful();
});

it('generates slug automatically from name', function () {
    $brand = Brand::factory()->create();

    Livewire::test(ProductResource\Pages\CreateProduct::class)
        ->fillForm([
            'name' => 'Test Product Name',
            'price' => 99.99,
            'sku' => 'TEST-001',
            'brand_id' => $brand->id,
            'status' => 'active',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('products', [
        'name' => 'Test Product Name',
        'slug' => 'test-product-name',
    ]);
});
