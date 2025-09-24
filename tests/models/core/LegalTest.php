<?php

declare(strict_types=1);

use App\Models\Legal;

it('instantiates Legal model', function (): void {
    expect(new Legal)->toBeInstanceOf(Legal::class);
});
