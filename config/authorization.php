<?php

declare(strict_types=1);

return [
    'guards' => [
        'admin',
        'web',
    ],

    'testing' => [
        'skip_checks' => true,
    ],

    'abilities' => [
        'panel' => [
            'access' => 'panel.access.admin',
        ],
        'products' => [
            'viewAny' => 'view_products',
            'view'    => 'view_products',
            'create'  => 'create_products',
            'update'  => 'edit_products',
            'delete'  => 'delete_products',
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
        'audit_logs' => [
            'viewAny' => 'audit_logs.viewAny',
        ],
        'product_histories' => [
            'viewAny' => 'product_histories.viewAny',
            'view'    => 'product_histories.view',
            'create'  => 'product_histories.create',
            'export'  => 'product_histories.export',
        ],
    ],

    'roles' => [
        'super_admin' => ['*'],
        'admin'       => [
            'panel.access.admin',
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'categories.viewAny', 'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'brands.viewAny', 'brands.view', 'brands.create', 'brands.update', 'brands.delete',
            'orders.viewAny', 'orders.view', 'orders.create', 'orders.update', 'orders.delete',
            'users.viewAny', 'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.viewAny', 'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            'audit_logs.viewAny',
            'product_histories.viewAny', 'product_histories.view', 'product_histories.create', 'product_histories.export',
        ],
        'administrator' => [
            'panel.access.admin',
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'categories.viewAny', 'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'brands.viewAny', 'brands.view', 'brands.create', 'brands.update', 'brands.delete',
            'orders.viewAny', 'orders.view', 'orders.create', 'orders.update', 'orders.delete',
            'users.viewAny', 'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.viewAny', 'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            'audit_logs.viewAny',
            'product_histories.viewAny', 'product_histories.view', 'product_histories.create', 'product_histories.export',
        ],
        'manager' => [
            'panel.access.admin',
            'view_products', 'create_products', 'edit_products',
            'categories.viewAny', 'categories.view', 'categories.create', 'categories.update',
            'brands.viewAny', 'brands.view', 'brands.update',
            'orders.viewAny', 'orders.view', 'orders.update',
            'users.viewAny', 'users.view',
        ],
        'editor' => [
            'panel.access.admin',
            'view_products', 'edit_products',
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
            'view_products',
            'categories.viewAny', 'categories.view',
            'brands.viewAny', 'brands.view',
            'orders.viewAny', 'orders.view',
        ],
        'user' => [],
    ],
];
