<?php

declare(strict_types=1);

use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Dataset capturing the primary and fallback columns used when ordering inventory records.
dataset('ordered_by_name_inventory', [
    [Inventory::class, ['sku', 'name', 'title']],
]);

it('orders inventory records by sku via the shared dataset', function (string $modelClass, array $columns): void {
    // Reset the table to avoid interference from factory seeds created elsewhere.
    $modelClass::query()->delete();

    $product = Product::factory()->create();
    $warehouse = Location::factory()->create();

    // Create records in reverse order to confirm the scope honours the configured sku column.
    $modelClass::factory()->create([
        'product_id'   => $product->id,
        'warehouse_id' => $warehouse->id,
        'sku'          => 'Z-999',
        'qty'          => 10,
    ]);

    $modelClass::factory()->create([
        'product_id'   => $product->id,
        'warehouse_id' => $warehouse->id,
        'sku'          => 'A-001',
        'qty'          => 5,
    ]);

    expect($modelClass::orderedByName()->pluck('sku')->all())->toBe(['A-001', 'Z-999']);
    expect($columns)->toBe(['sku', 'name', 'title']);
})->with('ordered_by_name_inventory');
