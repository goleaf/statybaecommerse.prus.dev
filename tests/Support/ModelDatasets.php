<?php

declare(strict_types=1);

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Dataset documenting which column combinations should be considered when
// running ordered-by-name regression tests for models using the shared trait.
dataset('ordered_by_name_models', function (): array {
    return [
        [Inventory::class, ['sku', 'name', 'title']],
    ];
});

// Dataset mapping Inventory relationships so matrix style tests can assert the
// relation methods exist and resolve to the expected relation classes.
dataset('inventory_relations_matrix', function (): array {
    return [
        Inventory::class => [
            'product'   => BelongsTo::class,
            'variant'   => BelongsTo::class,
            'warehouse' => BelongsTo::class,
            'location'  => BelongsTo::class,
            'movements' => HasMany::class,
        ],
    ];
});
