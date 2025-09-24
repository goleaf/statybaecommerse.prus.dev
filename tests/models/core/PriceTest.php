<?php

declare(strict_types=1);

use App\Models\Price;

it('instantiates Price model', function (): void {
    expect(new Price)->toBeInstanceOf(Price::class);
});
