<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\AdminAuthorizationSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->seed(AdminAuthorizationSeeder::class);

    $role = Role::findByName('admin', 'web');

    $this->adminUser = User::factory()->create([
        'email'    => 'admin@admin.com',
        'name'     => 'Admin User',
        'is_admin' => true,
    ]);

    $this->adminUser->assignRole($role);
});

it('redirects guests to admin login', function (): void {
    $this->get('/admin/s-e-o-analytics')
        ->assertRedirect('/admin/login');
});

it('mounts the page for authenticated admin', function (): void {
    $this->actingAs($this->adminUser)
        ->get('/admin/s-e-o-analytics')
        ->assertOk();
});

it('resolves route name and returns 200', function (): void {
    $this->actingAs($this->adminUser);

    $url = route('filament.admin.pages.s-e-o-analytics');

    $this->get($url)->assertOk();
});

// Table rendering is covered in other Filament resource tests. Here we focus on routing/auth.
