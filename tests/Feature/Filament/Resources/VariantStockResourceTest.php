<?php declare(strict_types=1);

use App\Filament\Resources\VariantStockResource\Pages\ListVariantStocks;
use App\Models\Location;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\VariantInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists variant inventory records', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $location = Location::factory()->create();
    $variant = ProductVariant::factory()->create();
    VariantInventory::factory()->create([
        'variant_id' => $variant->id,
        'location_id' => $location->id,
        'stock' => 9,
    ]);

    Livewire::test(ListVariantStocks::class)->assertOk();
});


