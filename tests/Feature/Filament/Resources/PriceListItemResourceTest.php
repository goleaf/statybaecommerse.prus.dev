<?php

declare(strict_types=1);

use App\Filament\Resources\PriceListItemResource\Pages\CreatePriceListItem;
use App\Filament\Resources\PriceListItemResource\Pages\EditPriceListItem;
use App\Filament\Resources\PriceListItemResource\Pages\ListPriceListItems;
use App\Models\Currency;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Resolve the Filament admin panel so Livewire components resolve bindings correctly.
    $this->resolveAdminPanel();

    // Authenticate a deterministic administrator so resource policies allow access in each scenario.
    $this->adminUser = User::factory()->create([
        'email'    => 'info@egisstatyba.lt',
        'is_admin' => true,
    ]);

    actingAs($this->adminUser);
});

it('feature: lists price list items via the Filament table component', function (): void {
    $currency = Currency::factory()->create(['code' => 'EUR']);

    // Seed a price list and a single discounted item for visibility assertions.
    $priceList = PriceList::factory()->create([
        'currency_id' => $currency->id,
        'name'        => 'Livewire Coverage List',
    ]);

    $product = Product::factory()->create(['name' => 'Coverage Product']);

    $item = PriceListItem::factory()->create([
        'price_list_id' => $priceList->id,
        'product_id'    => $product->id,
        'net_amount'    => 90,
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(ListPriceListItems::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords([$item]);
});

it('feature: creates a price list item through the Livewire form', function (): void {
    $currency = Currency::factory()->create(['code' => 'USD']);
    $priceList = PriceList::factory()->create([
        'currency_id' => $currency->id,
        'name'        => 'Creation Coverage List',
    ]);
    $product = Product::factory()->create(['name' => 'Creatable Product']);

    Livewire::actingAs($this->adminUser)
        ->test(CreatePriceListItem::class)
        ->fillForm([
            'price_list_id' => $priceList->id,
            'product_id'    => $product->id,
            'net_amount'    => 75,
            'is_active'     => true,
            'valid_from'    => now()->subDay()->toDateString(),
            'valid_until'   => now()->addDay()->toDateString(),
            'name'          => ['en' => 'New Coverage Item'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('price_list_items', [
        'price_list_id' => $priceList->id,
        'product_id'    => $product->id,
        'net_amount'    => 75,
    ]);
});

it('feature: edits an existing price list item', function (): void {
    $currency = Currency::factory()->create(['code' => 'GBP']);
    $priceList = PriceList::factory()->create([
        'currency_id' => $currency->id,
        'name'        => 'Editable Coverage List',
    ]);
    $product = Product::factory()->create(['name' => 'Editable Product']);

    $item = PriceListItem::factory()->create([
        'price_list_id' => $priceList->id,
        'product_id'    => $product->id,
        'net_amount'    => 60,
        'is_active'     => true,
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(EditPriceListItem::class, ['record' => $item->getRouteKey()])
        ->fillForm([
            'net_amount' => 55,
            'is_active'  => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('price_list_items', [
        'id'         => $item->id,
        'net_amount' => 55,
        'is_active'  => false,
    ]);
});
