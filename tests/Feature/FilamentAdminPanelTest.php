<?php declare(strict_types=1);

use App\Models\User;
use Spatie\Permission\Models\Role;

it('can access admin panel with authenticated user', function () {
    $admin = User::factory()->create([
        'name' => 'Test Admin',
        'email' => 'test@admin.com',
    ]);
    
    // Create admin role if it doesn't exist
    $adminRole = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
    
    // Create necessary permissions
    $permissions = ['view_admin_panel', 'view_any_product', 'view_any_user', 'view_any_order'];
    foreach ($permissions as $permission) {
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }
    
    $adminRole->syncPermissions($permissions);
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
        'name' => 'Test Admin',
        'email' => 'test@admin.com',
    ]);
    
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin->assignRole($adminRole);
    
    $response = $this->actingAs($admin)->get('/admin/products');
    
    $response->assertStatus(200);
});

it('can access dashboard', function () {
    $admin = User::factory()->create([
        'name' => 'Test Admin', 
        'email' => 'test@admin.com',
    ]);
    
    $adminRole = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
    $admin->assignRole($adminRole);
    
    $response = $this->actingAs($admin)->get('/admin');
    
    $response->assertStatus(200);
    $response->assertSee('Valdymo skydas');
});
