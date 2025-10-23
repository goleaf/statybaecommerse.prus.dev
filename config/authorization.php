<?php

declare(strict_types=1);

return [
    'guards' => [
        'admin',
        'web',
    ],

    'abilities' => [
        'panel' => [
            'access' => 'panel.access.admin',
        ],
        'products' => [
            'viewAny' => 'products.viewAny',
            'view'    => 'products.view',
            'create'  => 'products.create',
            'update'  => 'products.update',
            'delete'  => 'products.delete',
        ],
        'categories' => [
            'viewAny' => 'categories.viewAny',
            'view'    => 'categories.view',
            'create'  => 'categories.create',
            'update'  => 'categories.update',
            'delete'  => 'categories.delete',
        ],
        'brands' => [
            'viewAny' => 'brands.viewAny',
            'view'    => 'brands.view',
            'create'  => 'brands.create',
            'update'  => 'brands.update',
            'delete'  => 'brands.delete',
        ],
        'orders' => [
            'viewAny' => 'orders.viewAny',
            'view'    => 'orders.view',
            'create'  => 'orders.create',
            'update'  => 'orders.update',
            'delete'  => 'orders.delete',
        ],
        'users' => [
            'viewAny' => 'users.viewAny',
            'view'    => 'users.view',
            'create'  => 'users.create',
            'update'  => 'users.update',
            'delete'  => 'users.delete',
        ],
        'roles' => [
            'viewAny' => 'roles.viewAny',
            'view'    => 'roles.view',
            'create'  => 'roles.create',
            'update'  => 'roles.update',
            'delete'  => 'roles.delete',
        ],
    ],

    'roles' => [
        'super_admin' => ['*'],
        'admin'       => [
            'panel.access.admin',
            'products.viewAny', 'products.view', 'products.create', 'products.update', 'products.delete',
            'categories.viewAny', 'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'brands.viewAny', 'brands.view', 'brands.create', 'brands.update', 'brands.delete',
            'orders.viewAny', 'orders.view', 'orders.create', 'orders.update', 'orders.delete',
            'users.viewAny', 'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.viewAny', 'roles.view', 'roles.create', 'roles.update', 'roles.delete',
        ],
        'administrator' => [
            'panel.access.admin',
            'products.viewAny', 'products.view', 'products.create', 'products.update', 'products.delete',
            'categories.viewAny', 'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'brands.viewAny', 'brands.view', 'brands.create', 'brands.update', 'brands.delete',
            'orders.viewAny', 'orders.view', 'orders.create', 'orders.update', 'orders.delete',
            'users.viewAny', 'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.viewAny', 'roles.view', 'roles.create', 'roles.update', 'roles.delete',
        ],
        'manager' => [
            'panel.access.admin',
            'products.viewAny', 'products.view', 'products.create', 'products.update',
            'categories.viewAny', 'categories.view', 'categories.create', 'categories.update',
            'brands.viewAny', 'brands.view', 'brands.update',
            'orders.viewAny', 'orders.view', 'orders.update',
            'users.viewAny', 'users.view',
        ],
        'editor' => [
            'panel.access.admin',
            'products.viewAny', 'products.view', 'products.update',
            'categories.viewAny', 'categories.view', 'categories.update',
            'brands.viewAny', 'brands.view', 'brands.update',
        ],
        'support' => [
            'panel.access.admin',
            'orders.viewAny', 'orders.view', 'orders.update',
            'users.viewAny', 'users.view', 'users.update',
        ],
        'viewer' => [
            'panel.access.admin',
            'products.viewAny', 'products.view',
            'categories.viewAny', 'categories.view',
            'brands.viewAny', 'brands.view',
            'orders.viewAny', 'orders.view',
        ],
        'user' => [],
    ],
];
