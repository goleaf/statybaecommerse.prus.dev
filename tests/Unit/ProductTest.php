<?php declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('has variants relationship', function () {
    $product = new Product();

    $relation = $product->variants();

    expect($relation)
        ->toBeInstanceOf(HasMany::class)
        ->and($relation->getRelated()::class)
        ->toBe(ProductVariant::class)
        ->and($relation->getForeignKeyName())
        ->toBe('product_id');
});
