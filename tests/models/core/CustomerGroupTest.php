<?php

declare(strict_types=1);

use App\Models\CustomerGroup;

it('instantiates CustomerGroup model', function (): void {
    expect(new CustomerGroup)->toBeInstanceOf(CustomerGroup::class);
});
