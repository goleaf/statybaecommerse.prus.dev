<?php declare(strict_types=1);

use App\Filament\Pages\SEOAnalytics;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $permissions = [
        'view_admin_panel',
        'view_any_product',
        'view_any_brand',
        'view_any_category',
    ];

    foreach ($permissions as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $role->syncPermissions($permissions);

    $this->adminUser = User::factory()->create([
        'email' => 'admin@admin.com',
        'name' => 'Admin User',
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


