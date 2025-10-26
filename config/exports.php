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
                    'resolver' => static fn (Order $order): mixed => $order->number,
                ],
                'status' => [
                    'label'    => 'Status',
                    'type'     => 'string',
                    'resolver' => static fn (Order $order): mixed => $order->status,
                ],
                'payment_status' => [
                    'label'    => 'Payment Status',
                    'type'     => 'string',
                    'resolver' => static fn (Order $order): mixed => $order->payment_status,
                ],
                'total' => [
                    'label'    => 'Grand Total',
                    'type'     => 'currency',
                    'resolver' => static fn (Order $order): mixed => $order->total,
                ],
                'customer' => [
                    'label'    => 'Customer',
                    'type'     => 'string',
                    'resolver' => static fn (Order $order): mixed => $order->user instanceof User ? $order->user->name : null,
                ],
                'items' => [
                    'label'    => 'Items',
                    'type'     => 'integer',
                    'resolver' => static fn (Order $order): mixed => $order->items_count,
                ],
                'created_at' => [
                    'label'    => 'Created At',
                    'type'     => 'datetime',
                    'resolver' => static fn (Order $order): mixed => $order->created_at,
                ],
            ],
        ],
        'products' => [
            'model'   => Product::class,
            'columns' => [
                'sku' => [
                    'label'    => 'SKU',
                    'type'     => 'string',
                    'resolver' => static fn (Product $product): mixed => $product->sku,
                ],
                'name' => [
                    'label'    => 'Name',
                    'type'     => 'string',
                    'resolver' => static fn (Product $product): mixed => $product->name,
                ],
                'status' => [
                    'label'    => 'Status',
                    'type'     => 'string',
                    'resolver' => static fn (Product $product): mixed => $product->status,
                ],
                'price' => [
                    'label'    => 'Price',
                    'type'     => 'currency',
                    'resolver' => static fn (Product $product): mixed => $product->price,
                ],
                'stock' => [
                    'label'    => 'Stock',
                    'type'     => 'integer',
                    'resolver' => static fn (Product $product): mixed => $product->stock_quantity,
                ],
                'created_at' => [
                    'label'    => 'Created At',
                    'type'     => 'datetime',
                    'resolver' => static fn (Product $product): mixed => $product->created_at,
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
                    'resolver' => static fn (User $user): mixed => $user->name,
                ],
                'email' => [
                    'label'    => 'Email',
                    'type'     => 'string',
                    'resolver' => static fn (User $user): mixed => $user->email,
                ],
                'role' => [
                    'label'    => 'Role',
                    'type'     => 'string',
                    'resolver' => static fn (User $user): mixed => $user->roles->pluck('name')->join(', '),
                ],
                'created_at' => [
                    'label'    => 'Created At',
                    'type'     => 'datetime',
                    'resolver' => static fn (User $user): mixed => $user->created_at,
                ],
                'last_login_at' => [
                    'label'    => 'Last Login',
                    'type'     => 'datetime',
                    'resolver' => static fn (User $user): mixed => $user->last_login_at,
                ],
            ],
        ],
    ],
];
