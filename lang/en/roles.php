<?php

declare(strict_types=1);

return [
    'title'      => 'Roles',
    'plural'     => 'Roles',
    'single'     => 'Role',
    'navigation' => 'Roles',
    'fields'     => [
        'name'               => 'Name',
        'guard_name'         => 'Guard',
        'permissions_matrix' => 'Permissions',
        'permissions_count'  => 'Permissions',
    ],
    'sections' => [
        'general'     => 'Role Details',
        'permissions' => 'Permissions Matrix',
    ],
    'modules' => [
        'panel'      => 'Admin Panel',
        'products'   => 'Products',
        'categories' => 'Categories',
        'brands'     => 'Brands',
        'orders'     => 'Orders',
        'users'      => 'Users',
        'roles'      => 'Roles',
    ],
    'abilities' => [
        'access'  => 'Access',
        'viewAny' => 'View Any',
        'view'    => 'View',
        'create'  => 'Create',
        'update'  => 'Update',
        'delete'  => 'Delete',
    ],
];
