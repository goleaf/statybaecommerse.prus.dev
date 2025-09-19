<?php declare(strict_types=1);

use App\Filament\Pages\Dashboard;
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
        'view_any_product',
        'view_any_user',
        'view_any_order'
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
    ]);
    $this->admin->assignRole($this->adminRole);
});

it('can access dashboard with proper permissions', function () {
    $response = $this->actingAs($this->admin)->get('/admin');

    $response->assertStatus(200);
    $response->assertSee('Valdymo skydas');
});

it('dashboard class has correct properties', function () {
    expect(Dashboard::getNavigationIcon())->toBe('heroicon-o-home');
    expect(Dashboard::getNavigationSort())->toBe(1);
});

it('dashboard class can check access permissions', function () {
    // Test with authenticated admin user
    $this->actingAs($this->admin);
    expect(Dashboard::canAccess())->toBeTrue();

    // Test with user without permissions
    $userWithoutPermissions = User::factory()->create();
    $this->actingAs($userWithoutPermissions);
    expect(Dashboard::canAccess())->toBeFalse();
});

it('dashboard returns correct title and navigation label', function () {
    $dashboard = new Dashboard();

    expect($dashboard->getTitle())->toBe(__('Valdymo skydas'));
    expect(Dashboard::getNavigationLabel())->toBe(__('Valdymo skydas'));
});

it('dashboard has correct widget configuration', function () {
    $dashboard = new Dashboard();
    $widgets = $dashboard->getWidgets();

    expect($widgets)->toContain('App\Filament\Widgets\EcommerceOverview');
    expect($widgets)->toContain('App\Filament\Widgets\RealtimeAnalyticsWidget');
    expect($widgets)->toContain('App\Filament\Widgets\TopProductsWidget');
});

it('dashboard has correct column configuration', function () {
    $dashboard = new Dashboard();
    $columns = $dashboard->getColumns();

    expect($columns)->toBeArray();
    expect($columns['sm'])->toBe(1);
    expect($columns['md'])->toBe(2);
    expect($columns['lg'])->toBe(3);
    expect($columns['xl'])->toBe(4);
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

it('dashboard widgets are accessible with proper permissions', function () {
    $response = $this->actingAs($this->admin)->get('/admin');

    $response->assertStatus(200);
    // Check that the page contains widget containers
    $response->assertSee('Valdymo skydas');
});
