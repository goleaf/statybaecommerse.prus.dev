<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

it('can save product images with original repeater', function () {
    Storage::disk('public')->put('product-images/test-product.jpg', 'test-content');

    $component = Livewire::test(CreateProduct::class)
        ->fillForm([
            'name'     => 'Product Save Test',
            'slug'     => 'product-save-test',
            'sku'      => 'SAVE-001',
            'price'    => 100,
            'status'   => 'published',
            'brand_id' => $this->brand->id,
        ]);

    $imagesState = $component->get('data.images') ?? [];
    $firstKey = is_array($imagesState) ? array_key_first($imagesState) : null;

    if ($firstKey !== null) {
        $component
            ->set("data.images.{$firstKey}.path", ['product-images/test-product.jpg'])
            ->set("data.images.{$firstKey}.is_default", true);
    }

    $component->call('create')->assertHasNoFormErrors();

    $product = Product::withoutGlobalScopes()->where('sku', 'SAVE-001')->first();
    expect($product->images)->toHaveCount(1);
    expect($product->images->first()->is_default)->toBeTrue();
});

it('keeps uploaded product images on disk when editing', function () {
    $product = Product::factory()->create([
        'name'         => 'Editable Product',
        'slug'         => 'editable-product',
        'sku'          => 'EDIT-001',
        'price'        => 120,
        'status'       => 'published',
        'published_at' => now(),
        'is_visible'   => true,
        'brand_id'     => $this->brand->id,
    ]);

    Storage::disk('public')->put('product-images/edit-product.jpg', 'test-content');

    $component = Livewire::test(EditProduct::class, [
        'record' => $product->getRouteKey(),
    ]);

    $imagesState = $component->get('data.images') ?? [];
    $firstKey = is_array($imagesState) ? array_key_first($imagesState) : null;

    if ($firstKey !== null) {
        $component
            ->set("data.images.{$firstKey}.path", ['product-images/edit-product.jpg'])
            ->set("data.images.{$firstKey}.is_default", true);
    } else {
        $newKey = (string) Str::uuid();

        $component->set('data.images', [
            $newKey => [
                'path'       => ['product-images/edit-product.jpg'],
                'is_default' => true,
            ],
        ]);
    }

    $component->call('save')->assertHasNoFormErrors();

    $image = $product->images()->withoutGlobalScopes()->first();
    expect($image)->not->toBeNull();

    Storage::disk('public')->assertExists($image->path);
});
