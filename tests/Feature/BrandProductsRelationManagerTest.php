<?php

declare(strict_types=1);

use App\Filament\Resources\BrandResource\Pages\ViewBrand;
use App\Filament\Resources\BrandResource\RelationManagers\ProductsRelationManager;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows a default image for brand products without images', function (): void {
    $this->resolveAdminPanel();

    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $this->actingAs($admin);

    $brand = Brand::factory()->create();
    $product = Product::factory()->create([
        'brand_id' => $brand->id,
    ]);

    $placeholderUrl = product_placeholder_url('thumb');

    Livewire::test(ProductsRelationManager::class, [
        'ownerRecord' => $brand,
        'pageClass'   => ViewBrand::class,
    ])
        ->call('loadTable')
        ->assertCanSeeTableRecords([$product])
        ->assertSee($placeholderUrl, false);
});
