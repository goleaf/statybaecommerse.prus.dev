<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\DocumentTemplate;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\AdminAuthorizationSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(AdminAuthorizationSeeder::class);

    $extraPermissions = ['view_any_document_template'];

    foreach ($extraPermissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $adminRole = Role::findByName('administrator', 'web');
    $adminRole->givePermissionTo($extraPermissions);

    $this->admin = User::factory()->create([
        'name'     => 'Test Admin',
        'email'    => 'admin@test.com',
        'is_admin' => true,
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
