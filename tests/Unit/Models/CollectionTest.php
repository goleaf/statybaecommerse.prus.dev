<?php declare(strict_types=1);

use App\Models\Collection;

it('instantiates Collection model', function (): void {
    expect(new Collection())->toBeInstanceOf(Collection::class);
});
