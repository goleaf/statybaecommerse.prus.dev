<?php

declare(strict_types=1);

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

describe('Product resource authorization', function () {
    beforeEach(function (): void {
        config()->set('authorization.testing.skip_checks', false);

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
            'admin'       => ['view_products', 'create_products', 'edit_products', 'delete_products'],
            'manager'     => ['view_products', 'create_products', 'edit_products'],
            'editor'      => ['view_products', 'create_products', 'edit_products'],
            'user'        => [],
        ];

        foreach ($rolePermissions as $role => $permissions) {
            $roleModel = Role::findOrCreate($role, 'web');
            $roleModel->syncPermissions($permissions);
        }
    });

    afterEach(function (): void {
        config()->set('authorization.testing.skip_checks', true);
    });

    it('feature: returns 403 for users without product listing permission', function (string $role) {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user);

        expect($user->can('view_products'))->toBeFalse();
        expect(ProductResource::canViewAny())->toBeFalse();

        $this
            ->get(ProductResource::getUrl('index'))
            ->assertForbidden();
    })->with([
        'basic user' => 'user',
    ]);

    it('feature: blocks viewing a product without view permission', function (string $role) {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user);

        expect(ProductResource::resolveRecordRouteBinding($this->product->slug))->not()->toBeNull();

        $this
            ->get(ProductResource::getUrl('view', ['record' => $this->product]))
            ->assertForbidden();
    })->with([
        'basic user' => 'user',
    ]);

    it('feature: blocks access to the create page without create permission', function (string $role) {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this
            ->actingAs($user)
            ->get(ProductResource::getUrl('create'))
            ->assertForbidden();
    })->with([
        'basic user' => 'user',
    ]);

    it('feature: allows managers to access the create page', function (): void {
        $manager = User::factory()->create();
        $manager->assignRole('manager');

        $this
            ->actingAs($manager)
            ->get(ProductResource::getUrl('create'))
            ->assertOk();
    });

    it('feature: blocks editing without update permission', function (string $role) {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this
            ->actingAs($user)
            ->get(ProductResource::getUrl('edit', ['record' => $this->product]))
            ->assertForbidden();
    })->with([
        'basic user' => 'user',
    ]);

    it('feature: blocks delete table action without delete permission', function (string $role) {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user);

        expect(ProductResource::canDelete($this->product))->toBeFalse();
    })->with([
        'editor role missing delete ability'  => 'editor',
        'manager role missing delete ability' => 'manager',
        'basic user'                          => 'user',
    ]);
});
