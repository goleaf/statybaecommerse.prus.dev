<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\RoleResource\Pages\CreateRole;
use App\Models\Role;
use App\Models\User;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

final class RoleResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
        Config::set('auth.defaults.guard', 'web');

        $this->seedPermissions();

        $this->adminUser = User::factory()->create();
        $this->adminUser->givePermissionTo([
            'panel.access.admin',
            'roles.viewAny',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
        ]);
    }

    public function test_roles_table_has_permissions_matrix_column(): void
    {
        $this->assertTrue(
            Schema::hasColumn(config('permission.table_names.roles'), 'permissions_matrix')
        );
    }

    public function test_permissions_matrix_is_cast_to_array(): void
    {
        $role = Role::factory()->create([
            'permissions_matrix' => [
                'roles' => [
                    'viewAny' => true,
                    'view'    => false,
                ],
            ],
        ]);

        $matrix = $role->fresh()->permissions_matrix;

        $this->assertIsArray($matrix);
        $this->assertArrayHasKey('roles', $matrix);
        $this->assertTrue($matrix['roles']['viewAny']);
        $this->assertFalse($matrix['roles']['view']);
    }

    public function test_role_resource_persists_matrix_and_syncs_permissions(): void
    {
        $matrixState = [
            'panel' => ['access' => true],
            'roles' => [
                'viewAny' => true,
                'view'    => true,
                'create'  => true,
                'update'  => true,
                'delete'  => false,
            ],
        ];

        Livewire::actingAs($this->adminUser)
            ->test(CreateRole::class)
            ->fillForm([
                'name'               => 'content_manager',
                'guard_name'         => 'web',
                'permissions_matrix' => $matrixState,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $role = Role::where('name', 'content_manager')->firstOrFail();

        $this->assertTrue($role->permissions_matrix['roles']['viewAny']);
        $this->assertTrue($role->permissions_matrix['roles']['create']);
        $this->assertFalse($role->permissions_matrix['roles']['delete']);

        $permissions = $role->permissions()->pluck('name');

        $this->assertContains('panel.access.admin', $permissions);
        $this->assertContains('roles.viewAny', $permissions);
        $this->assertContains('roles.create', $permissions);
        $this->assertNotContains('roles.delete', $permissions);

        $pivotTable = config('permission.table_names.role_has_permissions');
        $permissionId = Permission::where('name', 'roles.create')->value('id');

        $this->assertDatabaseHas($pivotTable, [
            'role_id'       => $role->id,
            'permission_id' => $permissionId,
        ]);
    }

    private function seedPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (AuthorizationMatrix::allPermissions() as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
