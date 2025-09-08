<?php declare(strict_types=1);

use App\Models\Brand;

it('instantiates Brand model', function (): void {
    expect(new Brand())->toBeInstanceOf(Brand::class);
});
