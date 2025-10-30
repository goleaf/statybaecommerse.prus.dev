<?php

declare(strict_types=1);

use App\Filament\Resources\PriceListResource\Pages\ListPriceLists;
use App\Models\Currency;
use App\Models\PriceList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Register the Filament admin panel ahead of Livewire component assertions.
    $this->resolveAdminPanel();

    // Authenticate a deterministic administrator for resource access checks.
    $this->adminUser = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    actingAs($this->adminUser);
});

it('feature: lists price lists within the Filament table', function (): void {
    $currency = Currency::factory()->create(['code' => 'EUR']);

    // Persist a representative price list entry to confirm the table renders seeded records.
    $priceList = PriceList::factory()->create([
        'currency_id' => $currency->id,
        'name'        => 'Primary Price List',
    ]);

    Livewire::actingAs($this->adminUser)
        ->test(ListPriceLists::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords([$priceList]);
});
