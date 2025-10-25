<?php

declare(strict_types=1);

use App\Models\Price;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

it('product has brand and prices relations', function () {
    $product = new Product;

    expect($product->brand())
        ->toBeInstanceOf(BelongsTo::class)
        ->and($product->prices())
        ->toBeInstanceOf(MorphMany::class);
});

it('price belongs to currency', function () {
    $price = new Price;

    expect($price->currency())->toBeInstanceOf(BelongsTo::class);
});

it('product image belongs to product', function () {
    $image = new ProductImage;

    expect($image->product())->toBeInstanceOf(BelongsTo::class);
});

it('product image optionally belongs to a variant', function () {
    $image = new ProductImage;

    expect($image->variant())->toBeInstanceOf(BelongsTo::class);
});
