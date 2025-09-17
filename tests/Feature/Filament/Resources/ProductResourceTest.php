<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('lists products page', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    get('/admin/products')->assertOk();
});
