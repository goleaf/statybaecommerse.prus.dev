<?php

declare(strict_types=1);

use App\Models\User;

it('returns null when preferred locale not set', function () {
    $u = new User;
    expect($u->preferredLocale())->toBeNull();
});

it('returns preferred locale when set', function () {
    $u = new User;
    $u->setAttribute('preferred_locale', 'lt');
    expect($u->preferredLocale())->toBe('lt');
});
