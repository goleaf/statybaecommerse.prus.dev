<?php

declare(strict_types=1);

namespace App\Support\Authorization;

use App\Enums\AuthorizationRole;

class AuthorizationMatrix
{
    /**
     * Get all available permissions.
     *
     * @return array<string>
     */
    public static function allPermissions(): array
    {
        return [
            // User Management
            'view_users',
            'manage_users',
            'delete_users',
            'view_roles',
            'manage_roles',

            // Product Management
            'view_products',
            'manage_products',
            'delete_products',
            'manage_inventory',

            // Order Management
            'view_orders',
            'manage_orders',
            'process_refunds',

            // Content
            'manage_content',
            'manage_media',

            // Settings
            'view_settings',
            'manage_settings',
            'view_logs',
            'manage_system',

            // Reports
            'view_reports',
            'export_data',
        ];
    }

    /**
     * Get the guards that should have these permissions.
     *
     * @return array<string>
     */
    public static function guardNames(): array
    {
        return ['admin', 'web'];
    }

    /**
     * Get role definitions with their permissions.
     *
     * @return array<array{role: AuthorizationRole, permissions: array<string>}>
     */
    public static function roles(): array
    {
        $all = self::allPermissions();

        return [
            [
                'role'        => AuthorizationRole::SUPER_ADMIN,
                'permissions' => $all,
            ],
            [
                'role'        => AuthorizationRole::ADMIN,
                'permissions' => array_diff($all, ['manage_system', 'delete_users']),
            ],
            [
                'role'        => AuthorizationRole::ADMINISTRATOR,
                'permissions' => array_diff($all, ['manage_system', 'manage_roles']),
            ],
            [
                'role'        => AuthorizationRole::MANAGER,
                'permissions' => [
                    'view_users',
                    'view_products',
                    'manage_products',
                    'manage_inventory',
                    'view_orders',
                    'manage_orders',
                    'process_refunds',
                    'manage_content',
                    'manage_media',
                    'view_reports',
                ],
            ],
            [
                'role'        => AuthorizationRole::EDITOR,
                'permissions' => [
                    'view_products',
                    'manage_products',
                    'manage_content',
                    'manage_media',
                ],
            ],
            [
                'role'        => AuthorizationRole::SUPPORT,
                'permissions' => [
                    'view_users',
                    'view_orders',
                    'manage_orders',
                    'process_refunds',
                ],
            ],
            [
                'role'        => AuthorizationRole::VIEWER,
                'permissions' => [
                    'view_users',
                    'view_products',
                    'view_orders',
                    'view_reports',
                ],
            ],
            [
                'role'        => AuthorizationRole::USER,
                'permissions' => [],
            ],
        ];
    }
}
