<?php

declare(strict_types=1);

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Database\Seeders\InventorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates product inventories using factory relationships', function () {
    // Create prerequisite data
    Location::factory(2)->create();
    Product::factory(3)->create();

    $seeder = new InventorySeeder;
    $seeder->run();

    // Verify inventories were created
    expect(Inventory::count())->toBeGreaterThan(0);

    // Verify inventory relationships
    $inventory = Inventory::with(['product', 'location'])->first();
    expect($inventory->product)->not->toBeNull();
    expect($inventory->location)->not->toBeNull();
    expect($inventory->is_tracked)->toBeTrue();
    expect($inventory->quantity)->toBeGreaterThanOrEqual(0);
});

it('creates inventory for all product-location combinations', function () {
    // Create specific test data
    $locations = Location::factory(2)->create();
    $products = Product::factory(2)->create();

    $seeder = new InventorySeeder;
    $seeder->run();

    // Verify inventory exists for each product-location combination
    foreach ($products as $product) {
        foreach ($locations as $location) {
            $inventory = Inventory::where('product_id', $product->id)
                ->where('location_id', $location->id)
                ->first();

            expect($inventory)->not->toBeNull();
        }
    }
});

it('creates variant inventories when variant inventory table exists', function () {
    // Create prerequisite data
    Location::factory(2)->create();
    ProductVariant::factory(3)->create();

    $seeder = new InventorySeeder;
    $seeder->run();

    // If variant inventories table exists, verify variant inventories were created
    if (Schema::hasTable('variant_inventories')) {
        expect(VariantInventory::count())->toBeGreaterThan(0);

        $variantInventory = VariantInventory::with(['variant', 'location'])->first();
        expect($variantInventory->variant)->not->toBeNull();
        expect($variantInventory->location)->not->toBeNull();
        expect($variantInventory->stock)->toBeGreaterThanOrEqual(0);
    }
});

it('skips existing inventory records', function () {
    // Create prerequisite data
    $location = Location::factory()->create();
    $product = Product::factory()->create();

    // Create existing inventory
    Inventory::factory()
        ->for($product)
        ->for($location)
        ->create(['quantity' => 100]);

    $seeder = new InventorySeeder;
    $seeder->run();

    // Verify only one inventory record exists for this product-location
    $inventoryCount = Inventory::where('product_id', $product->id)
        ->where('location_id', $location->id)
        ->count();

    expect($inventoryCount)->toBe(1);

    // Verify the original quantity wasn't changed
    $inventory = Inventory::where('product_id', $product->id)
        ->where('location_id', $location->id)
        ->first();

    expect($inventory->quantity)->toBe(100);
});

it('handles missing locations gracefully', function () {
    // Create products but no locations
    Product::factory(2)->create();

    $seeder = new InventorySeeder;
    $seeder->run();

    // Verify seeder completes without error and no inventories are created
    expect(Inventory::count())->toBe(0);
});

it('uses factory states for realistic inventory data', function () {
    Location::factory(2)->create();
    Product::factory(3)->create();

    $seeder = new InventorySeeder;
    $seeder->run();

    // Verify inventory data is realistic
    $inventories = Inventory::all();

    foreach ($inventories as $inventory) {
        expect($inventory->is_tracked)->toBeTrue();
        expect($inventory->incoming)->toBeGreaterThanOrEqual(0);
        expect($inventory->incoming)->toBeLessThanOrEqual(20);
        expect($inventory->threshold)->toBeGreaterThanOrEqual(5);
        expect($inventory->threshold)->toBeLessThanOrEqual(15);
    }
});

it('processes inventories in chunks for performance', function () {
    // Create many products to test chunking
    Location::factory()->create();
    Product::factory(150)->create();

    $seeder = new InventorySeeder;
    $seeder->run();

    // Verify all products have inventory
    $productsWithoutInventory = Product::doesntHave('inventories')->count();
    expect($productsWithoutInventory)->toBe(0);

    // Verify total inventory count
    expect(Inventory::count())->toBe(150);  // 150 products × 1 location
});

it('maintains proper inventory relationships', function () {
    Location::factory(2)->create();
    Product::factory(3)->create();

    $seeder = new InventorySeeder;
    $seeder->run();

    // Verify all inventories have valid relationships
    $inventoriesWithoutProduct = Inventory::whereNull('product_id')->count();
    expect($inventoriesWithoutProduct)->toBe(0);

    $inventoriesWithoutLocation = Inventory::whereNull('location_id')->count();
    expect($inventoriesWithoutLocation)->toBe(0);

    // Verify foreign key constraints
    $inventoriesWithInvalidProduct = Inventory::whereNotExists(function ($query) {
        $query
            ->select('id')
            ->from('products')
            ->whereColumn('products.id', 'inventories.product_id');
    })->count();

    expect($inventoriesWithInvalidProduct)->toBe(0);
});
