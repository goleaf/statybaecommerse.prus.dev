<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
        'code' => 'EUR',
        'is_default' => true,
        'is_active' => true,
        'is_enabled' => true,
    ]);

    Storage::fake('public');
});

it('can save product images with original repeater', function () {
    $file = UploadedFile::fake()->image('test-product.jpg');

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Product Save Test',
            'slug' => 'product-save-test',
            'sku' => 'SAVE-001',
            'price' => 100,
            'status' => 'published',
            'brand_id' => $this->brand->id,
        ])
        ->set('data.images.item1.path', [$file]) // Use array for path
        ->set('data.images.item1.is_default', true)
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::where('sku', 'SAVE-001')->first();
    expect($product->images)->toHaveCount(1);
    expect($product->images->first()->is_default)->toBeTrue();
});