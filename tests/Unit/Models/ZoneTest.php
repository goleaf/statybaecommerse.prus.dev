<?php declare(strict_types=1);

use App\Models\Zone;

it('instantiates Zone model', function (): void {
    expect(new Zone())->toBeInstanceOf(Zone::class);
});
