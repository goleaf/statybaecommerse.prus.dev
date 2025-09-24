<?php

declare(strict_types=1);

use App\Models\User;

it('instantiates User model', function (): void {
    expect(new User)->toBeInstanceOf(User::class);
});
