<?php

declare(strict_types=1);

use App\Models\Price;
use App\Models\PriceList;
use App\Models\PriceListItem;
use App\Models\Product;
use App\Models\ProductVariant;
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

it('price list item belongs to price list product and variant', function () {
    $item = new PriceListItem;

    expect($item->priceList())
        ->toBeInstanceOf(BelongsTo::class)
        ->and($item->priceList()->getRelated())
        ->toBeInstanceOf(PriceList::class)
        ->and($item->product())
        ->toBeInstanceOf(BelongsTo::class)
        ->and($item->product()->getRelated())
        ->toBeInstanceOf(Product::class)
        ->and($item->variant())
        ->toBeInstanceOf(BelongsTo::class)
        ->and($item->variant()->getRelated())
        ->toBeInstanceOf(ProductVariant::class);
});
