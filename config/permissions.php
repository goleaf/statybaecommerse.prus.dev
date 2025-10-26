<?php

declare(strict_types=1);

return [
    'entities' => [
        'product'  => ['viewAny', 'view', 'create', 'update', 'delete', 'restore'],
        'category' => ['viewAny', 'view', 'create', 'update', 'delete', 'restore'],
        'brand'    => ['viewAny', 'view', 'create', 'update', 'delete', 'restore'],
        'order'    => ['viewAny', 'view', 'create', 'update', 'delete', 'restore'],
        'user'     => ['viewAny', 'view', 'create', 'update', 'delete', 'restore'],
    ],
    'roles' => [
        'admin' => [
            'product.*',
            'category.*',
            'brand.*',
            'order.*',
            'user.*',
        ],
        'manager' => [
            'product.viewAny',
            'product.view',
            'product.create',
            'product.update',
            'category.viewAny',
            'category.view',
            'category.create',
            'category.update',
            'brand.viewAny',
            'brand.view',
            'brand.create',
            'brand.update',
            'order.viewAny',
            'order.view',
            'order.update',
            'user.viewAny',
            'user.view',
        ],
        'editor' => [
            'product.viewAny',
            'product.view',
            'product.update',
            'category.viewAny',
            'category.view',
            'category.update',
            'brand.viewAny',
            'brand.view',
            'brand.update',
        ],
        'viewer' => [
            'product.viewAny',
            'product.view',
            'category.viewAny',
            'category.view',
            'brand.viewAny',
            'brand.view',
            'order.viewAny',
            'order.view',
        ],
    ],
    'aliases' => [
        'administrator' => 'admin',
        'super_admin'   => 'admin',
        'user'          => 'viewer',
    ],
];
