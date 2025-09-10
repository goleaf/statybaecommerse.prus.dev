<?php declare(strict_types=1);

use App\Filament\Resources\StockResource\Pages\ListStocks;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists inventory records', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $location = Location::factory()->create();
    $product = Product::factory()->create(['manage_stock' => true]);
    Inventory::factory()->create([
        'inventoriable_type' => Product::class,
        'inventoriable_id' => $product->id,
        'location_id' => $location->id,
        'quantity' => 7,
    ]);

    Livewire::test(ListStocks::class)->assertOk();
});

it('creates inventory record', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $location = Location::factory()->create();
    $product = Product::factory()->create(['manage_stock' => true]);

    $data = [
        'inventoriable_type' => Product::class,
        'inventoriable_id' => $product->id,
        'location_id' => $location->id,
        'quantity' => 5,
        'reserved' => 0,
        'incoming' => 0,
        'threshold' => 1,
        'is_tracked' => true,
    ];

    $this->post(route('filament.admin.resources.stocks.create'), $data)
        ->assertStatus(302);

    $this->assertDatabaseHas('inventories', [
        'inventoriable_type' => Product::class,
        'inventoriable_id' => $product->id,
        'location_id' => $location->id,
        'quantity' => 5,
    ]);
});


