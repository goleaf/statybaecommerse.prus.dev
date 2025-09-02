<?php declare(strict_types=1);

use App\Models\Product;

it('instantiates Product model', function (): void {
    expect(new Product())->toBeInstanceOf(Product::class);
});
