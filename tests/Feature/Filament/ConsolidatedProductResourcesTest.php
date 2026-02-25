<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\BrandResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CollectionResource;
use App\Filament\Resources\ProductRequestResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductVariantResource;
use App\Filament\Resources\VariantCombinationResource;
use App\Models\AdminUser;
use App\Models\Collection;
use App\Models\Currency;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = AdminUser::factory()->create();
    $this->actingAs($this->admin, 'admin');

    // Seed default currency
    Currency::factory()->create(['code' => 'EUR', 'is_default' => true]);
});

it('can list products', function () {
    livewire(ProductResource\Pages\ListProducts::class)
        ->assertSuccessful();
});

it('can list brands', function () {
    livewire(BrandResource\Pages\ListBrands::class)
        ->assertSuccessful();
});

it('can list categories', function () {
    livewire(CategoryResource\Pages\ListCategories::class)
        ->assertSuccessful();
});

it('can list collections', function () {
    livewire(CollectionResource\Pages\ListCollections::class)
        ->assertSuccessful();
});

it('can list product variants', function () {
    livewire(ProductVariantResource\Pages\ListProductVariants::class)
        ->assertSuccessful();
});

it('can list product requests', function () {
    livewire(ProductRequestResource\Pages\ListProductRequests::class)
        ->assertSuccessful();
});

it('can list variant combinations', function () {
    livewire(VariantCombinationResource\Pages\ListVariantCombinations::class)
        ->assertSuccessful();
});

it('can manage product variants from product', function () {
    $product = Product::factory()->create();

    livewire(ProductResource\RelationManagers\VariantsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => ProductResource\Pages\EditProduct::class,
    ])
        ->assertSuccessful();
});

it('can manage collections from product', function () {
    $product = Product::factory()->create();

    livewire(ProductResource\RelationManagers\CollectionsRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => ProductResource\Pages\EditProduct::class,
    ])
        ->assertSuccessful();
});

it('can manage products from collection', function () {
    $collection = Collection::factory()->create();

    livewire(CollectionResource\RelationManagers\ProductsRelationManager::class, [
        'ownerRecord' => $collection,
        'pageClass'   => CollectionResource\Pages\EditCollection::class,
    ])
        ->assertSuccessful();
});
