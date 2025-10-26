<?php

declare(strict_types=1);

use App\Models\Inventory;
use App\Models\PriceListItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserBehavior;
use App\Models\UserProductInteraction;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Dataset documenting which column combinations should be considered when
// running ordered-by-name regression tests for models using the shared trait.
dataset('ordered_by_name_models', function (): array {
    return [
        [Inventory::class, ['sku', 'name', 'title']],
        [PriceListItem::class, ['name']],
        [UserBehavior::class, ['event', 'name']],
        [UserProductInteraction::class, ['event', 'action', 'name']],
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

// Dataset documenting the UserBehavior relations so analytics coverage remains verified.
dataset('user_behavior_relations_matrix', function (): array {
    return [
        UserBehavior::class => [
            'user' => BelongsTo::class,
        ],
    ];
});

// Dataset documenting the UserProductInteraction relations so analytics coverage remains verified.
dataset('user_product_interaction_relations_matrix', function (): array {
    return [
        ['user', BelongsTo::class, User::class],
        ['product', BelongsTo::class, Product::class],
        ['variant', BelongsTo::class, ProductVariant::class],
    ];
});
