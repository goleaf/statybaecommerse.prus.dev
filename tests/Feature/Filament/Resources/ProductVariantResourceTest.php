<?php

declare(strict_types=1);

use App\Filament\Resources\ProductVariantResource\Pages\CreateProductVariants;
use App\Models\AdminUser;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->resolveAdminPanel();
    $this->withoutVite();

    $admin = AdminUser::factory()->create();
    $this->actingAs($admin, 'admin');
});

it('can render create page', function (): void {
    Livewire::test(CreateProductVariants::class)
        ->assertSuccessful();
});

it('can create a product variant', function (): void {
    $product = Product::factory()->create();

    Livewire::test(CreateProductVariants::class)
        ->fillForm([
            'product_id'     => $product->id,
            'sku'            => 'TEST-SKU-123',
            'name'           => 'Test Variant',
            'price'          => 19.99,
            'stock_quantity' => 10,
            'is_enabled'     => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('product_variants', [
        'product_id' => $product->id,
        'sku'        => 'TEST-SKU-123',
    ]);
});
