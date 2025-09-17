<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Guards
        $guard = 'web';

        // Roles
        $admin = Role::findOrCreate('administrator', $guard);
        // Backwards-compatible alias used by tests and some seeders
        $superAdmin = Role::findOrCreate('super_admin', $guard);
        $manager = Role::findOrCreate('manager', $guard);
        $user = Role::findOrCreate('user', $guard);

        // Permission groups and actions
        $groups = [
            'system',
            'brands',
            'categories',
            'collections',
            'products',
            'customers',
            'orders',
            'discounts',
            'reviews',
            'pricing',
            'attributes',
            'settings',
            'inventories',
        ];

        $actions = ['view', 'create', 'update', 'delete'];

        $permissions = [];
        foreach ($groups as $group) {
            foreach ($actions as $action) {
                $permissions[] = Permission::findOrCreate("{$action} {$group}", $guard);
            }
        }

        // Shopper-specific browse_* permissions used by admin UI
        $browse = [
            'browse_products',
            'browse_categories',
            'browse_collections',
            'browse_brands',
            'browse_orders',
            'browse_discounts',
            'browse_customers',
            'browse_attributes',
            'browse_inventories',
        ];
        foreach ($browse as $name) {
            $permissions[] = Permission::findOrCreate($name, $guard);
        }

        // Assign all to admin and super_admin
        $admin->syncPermissions($permissions);
        $superAdmin->syncPermissions($permissions);

        // Assign a subset to manager
        $manager->syncPermissions(collect($permissions)->filter(function ($perm) {
            return str_contains($perm->name, 'view') || str_contains($perm->name, 'update');
        })->values()->all());

        // User role: reserved for customers; no backend permissions by default
        $user->syncPermissions([]);
    }
}
