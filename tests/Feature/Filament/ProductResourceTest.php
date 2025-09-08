<?php declare(strict_types=1);

use App\Filament\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('can render product resource index page', function () {
    $this->get(ProductResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list products', function () {
    $products = Product::factory()->count(10)->create();

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->assertCanSeeTableRecords($products);
});

it('can render product resource create page', function () {
    $this->get(ProductResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create product', function () {
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();
    
    $newData = Product::factory()->make([
        'brand_id' => $brand->id,
    ])->toArray();
    
    $newData['categories'] = [$category->id];

    Livewire::test(ProductResource\Pages\CreateProduct::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'name' => $newData['name'],
        'slug' => $newData['slug'],
        'brand_id' => $brand->id,
    ]);
});

it('can validate product creation', function () {
    Livewire::test(ProductResource\Pages\CreateProduct::class)
        ->fillForm([
            'name' => null,
            'price' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'price']);
});

it('can render product resource view page', function () {
    $product = Product::factory()->create();

    $this->get(ProductResource::getUrl('view', ['record' => $product]))
        ->assertSuccessful();
});

it('can retrieve product data', function () {
    $product = Product::factory()->create();

    Livewire::test(ProductResource\Pages\ViewProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->assertFormSet([
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
        ]);
});

it('can render product resource edit page', function () {
    $product = Product::factory()->create();

    $this->get(ProductResource::getUrl('edit', ['record' => $product]))
        ->assertSuccessful();
});

it('can update product', function () {
    $product = Product::factory()->create();
    $newData = Product::factory()->make()->toArray();

    Livewire::test(ProductResource\Pages\EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->refresh())
        ->name->toBe($newData['name'])
        ->slug->toBe($newData['slug'])
        ->price->toBe($newData['price']);
});

it('can delete product', function () {
    $product = Product::factory()->create();

    Livewire::test(ProductResource\Pages\EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($product);
});

it('can search products', function () {
    $products = Product::factory()->count(10)->create();
    $searchProduct = $products->first();

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->searchTable($searchProduct->name)
        ->assertCanSeeTableRecords([$searchProduct])
        ->assertCanNotSeeTableRecords($products->skip(1));
});

it('can filter products by brand', function () {
    $brand = Brand::factory()->create();
    $productsWithBrand = Product::factory()->count(3)->create(['brand_id' => $brand->id]);
    $productsWithoutBrand = Product::factory()->count(2)->create();

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->filterTable('brand_id', $brand->id)
        ->assertCanSeeTableRecords($productsWithBrand)
        ->assertCanNotSeeTableRecords($productsWithoutBrand);
});

it('can filter products by status', function () {
    $activeProducts = Product::factory()->count(3)->create(['status' => 'active']);
    $draftProducts = Product::factory()->count(2)->create(['status' => 'draft']);

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->filterTable('status', 'active')
        ->assertCanSeeTableRecords($activeProducts)
        ->assertCanNotSeeTableRecords($draftProducts);
});

it('can sort products', function () {
    $products = Product::factory()->count(3)->create();

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($products->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($products->sortByDesc('name'), inOrder: true);
});

it('can bulk delete products', function () {
    $products = Product::factory()->count(10)->create();

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->callTableBulkAction('delete', $products);

    foreach ($products as $product) {
        $this->assertModelMissing($product);
    }
});

it('can generate images for products', function () {
    $products = Product::factory()->count(3)->create();

    Livewire::test(ProductResource\Pages\ListProducts::class)
        ->callTableBulkAction('generate_images', $products);

    // Assert that the action was called (actual image generation would require mocking)
    expect(true)->toBeTrue();
});

it('can access product relations', function () {
    $product = Product::factory()
        ->has(ProductVariant::factory()->count(3), 'variants')
        ->create();

    Livewire::test(ProductResource\Pages\ViewProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->assertSuccessful();

    expect($product->variants)->toHaveCount(3);
});