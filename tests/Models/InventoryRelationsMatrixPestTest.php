<?php

declare(strict_types=1);

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Dataset enumerating each relation and the expected Eloquent relation type so regressions surface quickly.
dataset('inventory_relations_matrix_pairs', [
    ['product', BelongsTo::class],
    ['variant', BelongsTo::class],
    ['warehouse', BelongsTo::class],
    ['movements', HasMany::class],
]);

it('exposes the documented relations via a dedicated matrix dataset', function (string $relation, string $expectedType): void {
    // Instantiate without touching the database because we only need the relation definitions themselves.
    $inventory = new Inventory;

    expect($inventory->{$relation}())->toBeInstanceOf($expectedType);
})->with('inventory_relations_matrix_pairs');
