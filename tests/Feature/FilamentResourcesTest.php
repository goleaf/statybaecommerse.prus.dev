<?php declare(strict_types=1);

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use App\Models\Order;
use App\Models\DocumentTemplate;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Create all necessary permissions
    $permissions = [
        'view_admin_panel', 'view_any_product', 'view_any_user', 'view_any_order',
        'view_any_brand', 'view_any_category', 'view_any_document_template',
        'create_product', 'edit_product', 'delete_product',
        'create_brand', 'edit_brand', 'delete_brand',
        'create_category', 'edit_category', 'delete_category',
    ];
    
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }
    
    // Create admin role with all permissions
    $adminRole = Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
    $adminRole->syncPermissions($permissions);
    
    // Create admin user
    $this->admin = User::factory()->create([
        'name' => 'Test Admin',
        'email' => 'admin@test.com',
    ]);
    $this->admin->assignRole($adminRole);
});

it('can access product resource index', function () {
    Product::factory()->count(3)->create();
    
    $response = $this->actingAs($this->admin)->get('/admin/products');
    
    $response->assertOk();
});

it('can access brand resource index', function () {
    Brand::factory()->count(3)->create();
    
    $response = $this->actingAs($this->admin)->get('/admin/brands');
    
    $response->assertOk();
});

it('can access category resource index', function () {
    Category::factory()->count(3)->create();
    
    $response = $this->actingAs($this->admin)->get('/admin/categories');
    
    $response->assertOk();
});

it('can access user resource index', function () {
    $response = $this->actingAs($this->admin)->get('/admin/users');
    
    $response->assertOk();
});

it('can access order resource index', function () {
    $response = $this->actingAs($this->admin)->get('/admin/orders');
    
    $response->assertOk();
});

it('can access document template resource index', function () {
    DocumentTemplate::factory()->count(2)->create();
    
    $response = $this->actingAs($this->admin)->get('/admin/document-templates');
    
    $response->assertOk();
});

it('admin dashboard shows widgets', function () {
    $response = $this->actingAs($this->admin)->get('/admin');
    
    $response->assertOk();
    $response->assertSee('Valdymo skydas');
});
