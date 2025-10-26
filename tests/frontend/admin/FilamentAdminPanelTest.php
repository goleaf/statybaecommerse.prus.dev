<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\AdminAuthorizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(AdminAuthorizationSeeder::class);
});

it('can access admin panel with authenticated user', function () {
    $admin = User::factory()->create([
        'name'     => 'Test Admin',
        'email'    => 'test@admin.com',
        'is_admin' => true,
    ]);

    $adminRole = Role::findByName('administrator', 'web');

    $admin->assignRole($adminRole);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
});

it('redirects unauthenticated users to login', function () {
    $response = $this->get('/admin');

    $response->assertRedirect();
    $response->assertRedirect('/admin/login');
});

it('can access product resource', function () {
    $admin = User::factory()->create([
        'name'     => 'Test Admin',
        'email'    => 'test@admin.com',
        'is_admin' => true,
    ]);

    $adminRole = Role::findByName('admin', 'web');
    $admin->assignRole($adminRole);

    $response = $this->actingAs($admin)->get('/admin/products');

    $response->assertStatus(200);
});

it('can access dashboard', function () {
    $admin = User::factory()->create([
        'name'     => 'Test Admin',
        'email'    => 'test@admin.com',
        'is_admin' => true,
    ]);

    $adminRole = Role::findByName('administrator', 'web');
    $admin->assignRole($adminRole);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
    $response->assertSee('Valdymo skydas');
});
