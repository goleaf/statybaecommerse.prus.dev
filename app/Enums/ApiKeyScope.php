<?php

declare(strict_types=1);

namespace App\Enums;

enum ApiKeyScope: string
{
    case ReadProducts = 'read:products';
    case WriteProducts = 'write:products';
    case ReadOrders = 'read:orders';
    case ManageOrders = 'manage:orders';
    case ManageCustomers = 'manage:customers';
    case AccessAnalytics = 'access:analytics';

    public function label(): string
    {
        return match ($this) {
            self::ReadProducts => __('api_keys.scopes.read_products'),
            self::WriteProducts => __('api_keys.scopes.write_products'),
            self::ReadOrders => __('api_keys.scopes.read_orders'),
            self::ManageOrders => __('api_keys.scopes.manage_orders'),
            self::ManageCustomers => __('api_keys.scopes.manage_customers'),
            self::AccessAnalytics => __('api_keys.scopes.access_analytics'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
