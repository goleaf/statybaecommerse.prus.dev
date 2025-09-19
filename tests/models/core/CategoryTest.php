<?php declare(strict_types=1);

use App\Models\Category;

it('instantiates Category model', function (): void {
    expect(new Category())->toBeInstanceOf(Category::class);
});
