<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\RelationManagers\ImagesRelationManager;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'is_admin' => true,
    ]);
    $this->actingAs($this->user);
    $this->brand = Brand::factory()->create();

    // Create default currency to avoid current_currency_model() returning null
    \App\Models\Currency::factory()->create([
        'code'       => 'EUR',
        'is_default' => true,
        'is_active'  => true,
        'is_enabled' => true,
    ]);

    Storage::fake('public');
});

it('creates product successfully without inline image repeater', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name'     => 'Product Save Test',
            'slug'     => 'product-save-test',
            'sku'      => 'SAVE-001',
            'price'    => 100,
            'status'   => 'published',
            'brand_id' => $this->brand->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::withoutGlobalScopes()->where('sku', 'SAVE-001')->first();
    expect($product)->not->toBeNull();
    expect($product?->images()->withoutGlobalScopes()->count())->toBe(0);
});

it('keeps uploaded product images on disk when creating through images relation manager', function () {
    $product = Product::factory()->create([
        'name'         => 'Editable Product',
        'slug'         => 'editable-product',
        'sku'          => 'EDIT-001',
        'price'        => 120,
        'status'       => 'published',
        'published_at' => now(),
        'brand_id'     => $this->brand->id,
    ]);

    Storage::disk('public')->put('product-images/edit-product.jpg', 'test-content');

    Livewire::test(ImagesRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => EditProduct::class,
    ])
        ->mountTableAction('create')
        ->set('mountedActions.0.data.path', ['product-images/edit-product.jpg'])
        ->set('mountedActions.0.data.is_default', true)
        ->set('mountedActions.0.data.is_active', true)
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    $image = $product->images()->withoutGlobalScopes()->latest('id')->first();
    expect($image)->not->toBeNull();

    Storage::disk('public')->assertExists($image?->path ?? '');
});
