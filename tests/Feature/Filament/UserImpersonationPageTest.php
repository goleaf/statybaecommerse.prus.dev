<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mounts the user impersonation page for admin', function (): void {
    $adminUser = User::factory()->create([
        'email' => 'admin@example.com',
        'is_admin' => true,
    ]);

    actingAs($adminUser);

    $this->get(route('filament.admin.pages.user-impersonation'))
        ->assertOk();
});

it('denies access to non-admin users', function (): void {
    $regularUser = User::factory()->create([
        'email' => 'user@example.com',
        'is_admin' => false,
    ]);

    actingAs($regularUser);

    $this->get(route('filament.admin.pages.user-impersonation'))
        ->assertForbidden();
});


