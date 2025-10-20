<?php

declare(strict_types=1);

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

describe('Product resource authorization', function () {
    beforeEach(function (): void {
        $this->resolveAdminPanel();

        $this->product = Product::factory()->create();

        foreach ([
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
        ] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $rolePermissions = [
            'super_admin' => ['view_products', 'create_products', 'edit_products', 'delete_products'],
            'admin' => ['view_products', 'create_products', 'edit_products', 'delete_products'],
            'manager' => ['view_products', 'edit_products'],
            'editor' => ['view_products', 'create_products', 'edit_products'],
            'user' => [],
        ];

        foreach ($rolePermissions as $role => $permissions) {
            $roleModel = Role::findOrCreate($role, 'web');
            $roleModel->syncPermissions($permissions);
        }
    });

    it('returns 403 for users without product listing permission', function (string $role) {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this
            ->actingAs($user)
            ->get(ProductResource::getUrl('index'))
            ->assertForbidden();
    })->with([
        'basic user' => 'user',
    ]);

    it('blocks viewing a product without view permission', function (string $role) {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this
            ->actingAs($user)
            ->get(ProductResource::getUrl('view', ['record' => $this->product]))
            ->assertForbidden();
    })->with([
        'basic user' => 'user',
    ]);

    it('blocks access to the create page without create permission', function (string $role) {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this
            ->actingAs($user)
            ->get(ProductResource::getUrl('create'))
            ->assertForbidden();
    })->with([
        'manager role missing create ability' => 'manager',
        'basic user' => 'user',
    ]);

    it('blocks editing without update permission', function (string $role) {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this
            ->actingAs($user)
            ->get(ProductResource::getUrl('edit', ['record' => $this->product]))
            ->assertForbidden();
    })->with([
        'basic user' => 'user',
    ]);

    it('blocks delete table action without delete permission', function (string $role) {
        $user = User::factory()->create();
        $user->assignRole($role);

        Livewire::actingAs($user)
            ->test(ListProducts::class)
            ->callTableAction('delete', $this->product)
            ->assertForbidden();
    })->with([
        'editor role missing delete ability' => 'editor',
        'manager role missing delete ability' => 'manager',
        'basic user' => 'user',
    ]);
});
