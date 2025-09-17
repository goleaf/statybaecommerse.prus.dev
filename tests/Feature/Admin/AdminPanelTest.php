<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('loads the admin dashboard without schema container errors', function (): void {
    $admin = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'is_admin' => true,
    ]);

    // Assign administrator role
    $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
    $admin->assignRole($adminRole);

    actingAs($admin);

    $response = get('/admin');

    $response->assertOk();
    $response->assertSee('admin');
});

it('switches locale via middleware and persists session', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    // Ensure default
    expect(app()->getLocale())->toBe(config('app.locale', 'lt'));

    $response = get(route('language.switch', ['locale' => 'en']));
    $response->assertRedirect();

    $this->followRedirects($response)->assertOk();

    expect(session('locale'))->toBe('en');
});
