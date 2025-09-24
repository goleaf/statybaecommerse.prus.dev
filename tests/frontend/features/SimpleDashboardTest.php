<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create necessary permissions
    $permissions = [
        'view_dashboard',
        'view_admin_panel',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    // Create admin role
    $this->adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $this->adminRole->syncPermissions($permissions);

    // Create admin user
    $this->admin = User::factory()->create([
        'name' => 'Test Admin',
        'email' => 'test@admin.com',
        'is_active' => true,
    ]);
    $this->admin->assignRole($this->adminRole);
});

it('can access dashboard with proper permissions', function () {
    $response = $this->actingAs($this->admin)->get('/admin');

    $response->assertStatus(200);
    $response->assertSee(__('admin.navigation.dashboard'));
});

it('redirects unauthenticated users to login', function () {
    $response = $this->get('/admin');

    $response->assertRedirect();
    $response->assertRedirect('/admin/login');
});

it('denies access to users without view_dashboard permission', function () {
    $userWithoutPermission = User::factory()->create();
    $response = $this->actingAs($userWithoutPermission)->get('/admin');

    $response->assertStatus(403);
});

it('dashboard class exists and has correct methods', function () {
    expect(class_exists('App\Filament\Pages\Dashboard'))->toBeTrue();

    $dashboard = new \App\Filament\Pages\Dashboard;
    expect(method_exists($dashboard, 'getTitle'))->toBeTrue();
    expect(method_exists($dashboard, 'getWidgets'))->toBeTrue();
    expect(method_exists($dashboard, 'getColumns'))->toBeTrue();
});

it('dashboard class can check access permissions', function () {
    // Test with authenticated admin user
    $this->actingAs($this->admin);
    expect(\App\Filament\Pages\Dashboard::canAccess())->toBeTrue();

    // Test with user without permissions
    $userWithoutPermissions = User::factory()->create();
    $this->actingAs($userWithoutPermissions);
    expect(\App\Filament\Pages\Dashboard::canAccess())->toBeFalse();
});

it('dashboard returns correct title', function () {
    $dashboard = new \App\Filament\Pages\Dashboard;
    expect($dashboard->getTitle())->toBe(__('admin.navigation.dashboard'));
});

it('dashboard has navigation properties', function () {
    expect(\App\Filament\Pages\Dashboard::getNavigationIcon())->toBe('heroicon-o-home');
    expect(\App\Filament\Pages\Dashboard::getNavigationSort())->toBe(1);
    expect(\App\Filament\Pages\Dashboard::getNavigationLabel())->toBe(__('admin.navigation.dashboard'));
});

it('dashboard has column configuration', function () {
    $dashboard = new \App\Filament\Pages\Dashboard;
    $columns = $dashboard->getColumns();

    expect($columns)->toBeArray();
    expect($columns['sm'])->toBe(1);
    expect($columns['md'])->toBe(2);
    expect($columns['lg'])->toBe(3);
    expect($columns['xl'])->toBe(4);
});

it('dashboard widgets configuration is accessible', function () {
    $dashboard = new \App\Filament\Pages\Dashboard;
    $widgets = $dashboard->getWidgets();

    expect($widgets)->toBeArray();
    // Widgets are now enabled, should have 3 widgets
    expect(count($widgets))->toBe(3);
});
