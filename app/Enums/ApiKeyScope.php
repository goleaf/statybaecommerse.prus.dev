<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Support\Collection;

enum ApiKeyScope: string
{
    case OrdersRead = 'orders.read';
    case OrdersWrite = 'orders.write';
    case ProductsRead = 'products.read';
    case ProductsWrite = 'products.write';
    case CustomersRead = 'customers.read';
    case CustomersWrite = 'customers.write';
    case AnalyticsRead = 'analytics.read';

    public function label(): string
    {
        return match ($this) {
            self::OrdersRead     => __('api_keys.scopes.orders_read.label'),
            self::OrdersWrite    => __('api_keys.scopes.orders_write.label'),
            self::ProductsRead   => __('api_keys.scopes.products_read.label'),
            self::ProductsWrite  => __('api_keys.scopes.products_write.label'),
            self::CustomersRead  => __('api_keys.scopes.customers_read.label'),
            self::CustomersWrite => __('api_keys.scopes.customers_write.label'),
            self::AnalyticsRead  => __('api_keys.scopes.analytics_read.label'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::OrdersRead     => __('api_keys.scopes.orders_read.description'),
            self::OrdersWrite    => __('api_keys.scopes.orders_write.description'),
            self::ProductsRead   => __('api_keys.scopes.products_read.description'),
            self::ProductsWrite  => __('api_keys.scopes.products_write.description'),
            self::CustomersRead  => __('api_keys.scopes.customers_read.description'),
            self::CustomersWrite => __('api_keys.scopes.customers_write.description'),
            self::AnalyticsRead  => __('api_keys.scopes.analytics_read.description'),
        };
    }

    public static function options(): array
    {
        return Collection::make(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function descriptions(): array
    {
        return Collection::make(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->description()])
            ->toArray();
    }
}
