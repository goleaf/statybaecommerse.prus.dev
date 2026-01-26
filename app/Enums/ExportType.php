<?php

declare(strict_types=1);

namespace App\Enums;

enum ExportType: string
{
    case ORDERS = 'orders';
    case PRODUCTS = 'products';
    case USERS = 'users';

    public function label(): string
    {
        return match ($this) {
            self::ORDERS   => __('Orders'),
            self::PRODUCTS => __('messages.products'),
            self::USERS    => __('Users'),
        };
    }
}
