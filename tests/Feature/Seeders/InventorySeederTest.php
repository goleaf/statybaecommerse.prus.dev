<?php

declare(strict_types=1);

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Database\Seeders\InventorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates product inventories for every product and warehouse', function () {
    $warehouses = Location::factory()->count(2)->warehouse()->create();
    $products = Product::factory()->count(3)->create();

    (new InventorySeeder)->run();

    foreach ($products as $product) {
        foreach ($warehouses as $warehouse) {
            $inventory = Inventory::query()
                ->where('product_id', $product->id)
                ->where('location_id', $warehouse->id)
                ->first();

            expect($inventory)->not->toBeNull();
            expect($inventory?->is_tracked)->toBeTrue();
        }
    }
});

it('creates missing warehouses and products when database is empty', function () {
    expect(Location::query()->count())->toBe(0);
    expect(Product::query()->count())->toBe(0);

    (new InventorySeeder)->run();

    expect(Location::query()->where('type', 'warehouse')->count())->toBeGreaterThan(0);
    expect(Product::query()->count())->toBeGreaterThan(0);
    expect(Inventory::query()->count())->toBeGreaterThan(0);
});

it('does not duplicate existing inventory rows', function () {
    $warehouse = Location::factory()->warehouse()->create();
    $product = Product::factory()->create();

    Inventory::factory()
        ->for($product)
        ->for($warehouse, 'warehouse')
        ->create([
            'qty' => 100,
        ]);

    (new InventorySeeder)->run();

    $inventoryRows = Inventory::query()
        ->where('product_id', $product->id)
        ->where('location_id', $warehouse->id)
        ->get();

    expect($inventoryRows)->toHaveCount(1);
    expect($inventoryRows->first()->qty)->toBe(100);
});

it('creates variant inventories for every variant and warehouse when table exists', function () {
    if (! Schema::hasTable('variant_inventories')) {
        test()->markTestSkipped('variant_inventories table is not available.');
    }

    Location::factory()->count(2)->warehouse()->create();
    ProductVariant::factory()->count(3)->create();

    (new InventorySeeder)->run();

    expect(VariantInventory::query()->count())->toBeGreaterThan(0);
});
