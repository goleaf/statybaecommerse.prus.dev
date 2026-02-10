<?php

declare(strict_types=1);

use App\Filament\Resources\PriceResource\Pages\CreatePrice;
use App\Filament\Resources\PriceResource\Pages\EditPrice;
use App\Filament\Resources\PriceResource\Pages\ListPrices;
use App\Models\Currency;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->resolveAdminPanel();

    $this->adminUser = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    $this->currency = Currency::factory()->create(['id' => 1, 'code' => 'EUR']);

    actingAs($this->adminUser);
});

it('can list prices', function (): void {
    $product = Product::factory()->create(['name' => 'Test Product']);
    $price = Price::factory()->create([
        'priceable_id'   => $product->id,
        'priceable_type' => Product::class,
        'amount'         => 99.99,
        'currency_id'    => $this->currency->id,
    ]);

    Livewire::test(ListPrices::class)
        ->assertCanSeeTableRecords([$price])
        ->assertTableColumnExists('priceable.name')
        ->assertTableColumnExists('amount')
        ->assertTableColumnExists('currency.code');
});

it('can create a price for a product', function (): void {
    $product = Product::factory()->create(['name' => 'New Product']);

    Livewire::test(CreatePrice::class)
        ->fillForm([
            'priceable_type' => Product::class,
            'priceable_id'   => $product->id,
            'currency_id'    => $this->currency->id,
            'amount'         => 150.00,
            'type'           => 'default',
            'is_enabled'     => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Price::class, [
        'priceable_type' => Product::class,
        'priceable_id'   => $product->id,
        'amount'         => 150.00,
    ]);
});

it('can create a price for a product variant', function (): void {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'New Variant']);

    Livewire::test(CreatePrice::class)
        ->fillForm([
            'priceable_type' => ProductVariant::class,
            'priceable_id'   => $variant->id,
            'currency_id'    => $this->currency->id,
            'amount'         => 75.50,
            'type'           => 'wholesale',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Price::class, [
        'priceable_type' => ProductVariant::class,
        'priceable_id'   => $variant->id,
        'amount'         => 75.50,
    ]);
});

it('can edit a price', function (): void {
    $product = Product::factory()->create();
    $price = Price::factory()->create([
        'priceable_id'   => $product->id,
        'priceable_type' => Product::class,
        'amount'         => 50.00,
        'currency_id'    => $this->currency->id,
    ]);

    Livewire::test(EditPrice::class, [
        'record' => $price->getRouteKey(),
    ])
        ->fillForm([
            'amount' => 55.00,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($price->refresh()->amount)->toBe('55.0000');
});

it('can validate price data', function (): void {
    Livewire::test(CreatePrice::class)
        ->fillForm([
            'amount' => -10, // Invalid negative price
        ])
        ->call('create')
        ->assertHasErrors(['amount']);
});
