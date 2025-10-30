<?php

declare(strict_types=1);

use App\Filament\Resources\PriceListResource;
use App\Filament\Resources\PriceListResource\Pages\ListPriceLists;
use App\Models\Currency;
use App\Models\PriceList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
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

it('feature: lists price lists via the Filament table component', function (): void {
    // Ensure Filament resolves the admin panel context before Livewire boots the component.
    test()->resolveAdminPanel();

    // Authenticate as an administrator so the resource policies grant table access.
    $admin = User::factory()->create([
        'email'    => 'admin@example.com',
        'is_admin' => true,
    ]);

    actingAs($admin);

    // Seed the currency relationship explicitly to avoid relying on optional migrations.
    $currency = Currency::factory()->create([
        'code'       => 'EUR',
        'is_default' => true,
        'is_enabled' => true,
    ]);

    // Seed a deterministic price list so the listing has a visible record to assert against.
    $attributes = [
        'name'        => 'Coverage Price List',
        'currency_id' => $currency->getKey(),
        'is_enabled'  => true,
        'priority'    => 1,
    ];

    // Only set optional scheduling flags when the migration exposed the columns.
    if (Schema::hasColumn('price_lists', 'starts_at')) {
        $attributes['starts_at'] = now()->subDay();
    }

    if (Schema::hasColumn('price_lists', 'ends_at')) {
        $attributes['ends_at'] = now()->addDay();
    }

    if (Schema::hasColumn('price_lists', 'is_default')) {
        $attributes['is_default'] = false;
    }

    if (Schema::hasColumn('price_lists', 'auto_apply')) {
        $attributes['auto_apply'] = false;
    }

    $priceList = PriceList::query()->create($attributes);

    // Hydrate the table data before asserting the seeded price list is present.
    Livewire::actingAs($admin)
        ->test(ListPriceLists::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords([$priceList]);
});
