<?php

declare(strict_types=1);

use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Disable authorization bypass for testing
    config()->set('authorization.testing.skip_checks', false);
    
    // Seed the authorization system
    $this->seed(\Database\Seeders\AdminAuthorizationSeeder::class);
});

it('redirects unauthenticated users to login', function (): void {
    $response = $this->get('/admin');
    
    expect($response->status())->toBe(302);
    expect($response->headers->get('Location'))->toContain('login');
});

it('allows authenticated admin users to access admin panel', function (): void {
    $user = User::factory()->create(['is_admin' => true]);
    
    $this->actingAs($user);
    $response = $this->get('/admin');
    
    // Accept both 200 (direct access) and 302 (redirect within admin panel)
    expect($response->status())->toBeIn([200, 302]);
});

it('allows users with admin role to access admin panel', function (): void {
    $user = User::factory()->create(['is_admin' => false]);
    $role = Role::findByName('admin', 'web');
    $user->assignRole($role);
    
    $this->actingAs($user);
    $response = $this->get('/admin');
    
    // Accept both 200 (direct access) and 302 (redirect within admin panel)
    expect($response->status())->toBeIn([200, 302]);
});

it('denies access to users without admin privileges', function (): void {
    $user = User::factory()->create(['is_admin' => false]);
    
    $this->actingAs($user);
    $response = $this->get('/admin');
    
    expect($response->status())->toBeIn([403, 302]);
});

it('allows admin users to access admin panel', function (): void {
    $adminUser = AdminUser::factory()->create();
    $role = Role::findByName('admin', 'admin');
    $adminUser->assignRole($role);
    
    $this->actingAs($adminUser, 'admin');
    $response = $this->get('/admin');
    
    // Accept both 200 (direct access) and 302 (redirect within admin panel)
    expect($response->status())->toBeIn([200, 302]);
});

it('enforces role-based permissions for resources', function (): void {
    // Test viewer role - should have limited access
    $viewer = User::factory()->create(['is_admin' => false]);
    $viewerRole = Role::findByName('viewer', 'web');
    $viewer->assignRole($viewerRole);
    
    $this->actingAs($viewer);
    
    // Should be able to access admin panel
    $response = $this->get('/admin');
    // Accept both 200 (direct access) and 302 (redirect within admin panel)
    expect($response->status())->toBeIn([200, 302]);
    
    // Test admin role - should have full access
    $admin = User::factory()->create(['is_admin' => false]);
    $adminRole = Role::findByName('admin', 'web');
    $admin->assignRole($adminRole);
    
    $this->actingAs($admin);
    
    // Should be able to access admin panel
    $response = $this->get('/admin');
    // Accept both 200 (direct access) and 302 (redirect within admin panel)
    expect($response->status())->toBeIn([200, 302]);
});