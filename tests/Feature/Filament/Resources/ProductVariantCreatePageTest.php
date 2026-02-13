<?php

declare(strict_types=1);

use App\Filament\Resources\ProductVariantResource;
use App\Filament\Resources\ProductVariantResource\Pages\CreateProductVariant;
use App\Filament\Resources\ProductVariantResource\Pages\CreateProductVariants;
use App\Models\AdminUser;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->resolveAdminPanel();
    $this->withoutVite();

    $this->admin = AdminUser::factory()->create();
    $this->actingAs($this->admin, 'admin');

    $this->product = Product::factory()->create();
});

it('renders product variant create page', function (): void {
    $response = $this->get(ProductVariantResource::getUrl('create'));

    $response->assertSuccessful();
});

it('creates a product variant from the create page', function (): void {
    $sku = 'PV-CRT-' . fake()->unique()->numerify('####');

    Livewire::test(CreateProductVariants::class)
        ->fillForm([
            'product_id'         => $this->product->id,
            'sku'                => $sku,
            'name'               => 'Create Page Variant',
            'price'              => 49.99,
            'stock_quantity'     => 12,
            'track_inventory'    => true,
            'is_enabled'         => true,
            'is_default_variant' => true,
            'allow_backorder'    => false,
            'variant_name_lt'    => 'Sukurtas variantas',
            'variant_name_en'    => 'Created Variant',
            'description_lt'     => 'Lietuviškas aprašymas',
            'description_en'     => 'English description',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $created = ProductVariant::query()
        ->withoutGlobalScopes()
        ->where('sku', $sku)
        ->first();

    expect($created)->not->toBeNull();
    expect($created?->product_id)->toBe($this->product->id);
    expect($created?->available_quantity)->toBe(12);
});

it('creates a product variant when optional translated columns are missing', function (): void {
    if (! Schema::hasColumn('product_variants', 'variant_name_lt')) {
        $this->markTestSkipped('variant_name_lt column is not available in the current schema.');
    }

    Schema::table('product_variants', function (Blueprint $table): void {
        $table->dropColumn('variant_name_lt');
    });

    $sku = 'PV-DRIFT-' . fake()->unique()->numerify('####');

    Livewire::test(CreateProductVariants::class)
        ->fillForm([
            'product_id'         => $this->product->id,
            'sku'                => $sku,
            'name'               => 'Schema Drift Variant',
            'price'              => 39.95,
            'stock_quantity'     => 4,
            'variant_name_lt'    => 'Neegzistuojantis stulpelis',
            'is_default_variant' => true,
            'is_enabled'         => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $created = ProductVariant::query()
        ->withoutGlobalScopes()
        ->where('sku', $sku)
        ->first();

    expect($created)->not->toBeNull();
    expect($created?->product_id)->toBe($this->product->id);
});

it('creates a product variant with safe defaults when status flags are omitted', function (): void {
    $sku = 'PV-DEF-' . fake()->unique()->numerify('####');

    Livewire::test(CreateProductVariants::class)
        ->fillForm([
            'product_id'     => $this->product->id,
            'sku'            => $sku,
            'name'           => 'Default Flags Variant',
            'price'          => 33.50,
            'stock_quantity' => 7,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $created = ProductVariant::query()
        ->withoutGlobalScopes()
        ->where('sku', $sku)
        ->first();

    expect($created)->not->toBeNull();
    expect((bool) $created?->is_enabled)->toBeTrue();
    expect((bool) $created?->is_default_variant)->toBeFalse();
    expect((bool) $created?->is_featured)->toBeFalse();
    expect((bool) $created?->is_new)->toBeFalse();
    expect((bool) $created?->is_bestseller)->toBeFalse();
    expect((bool) $created?->track_inventory)->toBeTrue();
    expect((bool) $created?->allow_backorder)->toBeFalse();
});

it('legacy singular create page uses the same safe defaults', function (): void {
    $sku = 'PV-LEG-' . fake()->unique()->numerify('####');

    Livewire::test(CreateProductVariant::class)
        ->fillForm([
            'product_id'     => $this->product->id,
            'sku'            => $sku,
            'name'           => 'Legacy Create Variant',
            'price'          => 29.00,
            'stock_quantity' => 5,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $created = ProductVariant::query()
        ->withoutGlobalScopes()
        ->where('sku', $sku)
        ->first();

    expect($created)->not->toBeNull();
    expect((bool) $created?->is_enabled)->toBeTrue();
});
