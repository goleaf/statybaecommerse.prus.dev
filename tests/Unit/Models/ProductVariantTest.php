<?php declare(strict_types=1);

use App\Models\ProductVariant;

it('instantiates ProductVariant model', function (): void {
    expect(new ProductVariant())->toBeInstanceOf(ProductVariant::class);
});
