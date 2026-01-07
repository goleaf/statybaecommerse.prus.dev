<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\Product;
use App\Models\User;

return [
    'path'        => storage_path('app/exports'),
    'chunk_size'  => 1000,
    'ttl_minutes' => 60 * 24,
    'entities'    => [
        'orders' => [
            'model'      => Order::class,
            'with'       => ['user'],
            'with_count' => ['items'],
            'columns'    => [
                'number' => [
                    'label'    => 'Order Number',
                    'type'     => 'string',
                    'resolver' => 'number',
                ],
                'status' => [
                    'label'    => 'Status',
                    'type'     => 'string',
                    'resolver' => 'status',
                ],
                'payment_status' => [
                    'label'    => 'Payment Status',
                    'type'     => 'string',
                    'resolver' => 'payment_status',
                ],
                'total' => [
                    'label'    => 'Grand Total',
                    'type'     => 'currency',
                    'resolver' => 'total',
                ],
                'customer' => [
                    'label'    => 'Customer',
                    'type'     => 'string',
                    'resolver' => 'user.name',
                ],
                'items' => [
                    'label'    => 'Items',
                    'type'     => 'integer',
                    'resolver' => 'items_count',
                ],
                'created_at' => [
                    'label'    => 'Created At',
                    'type'     => 'datetime',
                    'resolver' => 'created_at',
                ],
            ],
        ],
        'products' => [
            'model'   => Product::class,
            'columns' => [
                'sku' => [
                    'label'    => 'SKU',
                    'type'     => 'string',
                    'resolver' => 'sku',
                ],
                'name' => [
                    'label'    => 'Name',
                    'type'     => 'string',
                    'resolver' => 'name',
                ],
                'status' => [
                    'label'    => 'Status',
                    'type'     => 'string',
                    'resolver' => 'status',
                ],
                'price' => [
                    'label'    => 'Price',
                    'type'     => 'currency',
                    'resolver' => 'price',
                ],
                'stock' => [
                    'label'    => 'Stock',
                    'type'     => 'integer',
                    'resolver' => 'stock_quantity',
                ],
                'created_at' => [
                    'label'    => 'Created At',
                    'type'     => 'datetime',
                    'resolver' => 'created_at',
                ],
            ],
        ],
        'users' => [
            'model'   => User::class,
            'with'    => ['roles'],
            'columns' => [
                'name' => [
                    'label'    => 'Name',
                    'type'     => 'string',
                    'resolver' => 'name',
                ],
                'email' => [
                    'label'    => 'Email',
                    'type'     => 'string',
                    'resolver' => 'email',
                ],
                'role' => [
                    'label'    => 'Role',
                    'type'     => 'string',
                    'resolver' => 'roles.name',
                ],
                'created_at' => [
                    'label'    => 'Created At',
                    'type'     => 'datetime',
                    'resolver' => 'created_at',
                ],
                'last_login_at' => [
                    'label'    => 'Last Login',
                    'type'     => 'datetime',
                    'resolver' => 'last_login_at',
                ],
            ],
        ],
    ],
];
