<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('redirects unauthenticated users for exports', function (): void {
    $this->get('/exports')->assertRedirect();
});

it('forbids access without permission when authenticated', function (): void {
    Storage::fake('public');
    actingAs(User::factory()->create());
    $this->get('/exports')->assertForbidden();
});

it('forbids download without permission even when authenticated', function (): void {
    Storage::fake('public');
    actingAs(User::factory()->create());
    $this->get('/exports/test.csv')->assertForbidden();
});
