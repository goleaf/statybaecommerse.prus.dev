<?php

declare(strict_types=1);

namespace App\Enums;

enum NavigationGroup: string
{
    case UserManagement = 'user-management';
    case ContentManagement = 'content-management';
    case Ecommerce = 'ecommerce';
    case System = 'system';
    case Analytics = 'analytics';
    case Marketing = 'marketing';
    case Reports = 'reports';
    case Settings = 'settings';

    // Additional cases used in resources
    case Users = 'users';
    case Products = 'products';
    case Orders = 'orders';
    case Inventory = 'inventory';
    case Content = 'content';
    case Locations = 'locations';
    case Discounts = 'discounts';
    case Campaigns = 'campaigns';
    case News = 'news';
    case Referral = 'referral';

    public function getLabel(): string
    {
        return match ($this) {
            self::UserManagement    => __('messages.navigation_groups.user_management'),
            self::ContentManagement => __('messages.navigation_groups.content_management'),
            self::Ecommerce         => __('messages.navigation_groups.ecommerce'),
            self::System            => __('messages.navigation_groups.system'),
            self::Analytics         => __('messages.navigation_groups.analytics'),
            self::Marketing         => __('messages.navigation_groups.marketing'),
            self::Reports           => __('messages.navigation_groups.reports'),
            self::Settings          => __('messages.navigation_groups.settings'),
            self::Users             => __('messages.navigation_groups.users'),
            self::Products          => __('messages.navigation_groups.products'),
            self::Orders            => __('messages.navigation_groups.orders'),
            self::Inventory         => __('messages.navigation_groups.inventory'),
            self::Content           => __('messages.navigation_groups.content'),
            self::Locations         => __('messages.navigation_groups.locations'),
            self::Discounts         => __('messages.navigation_groups),
            self::Campaigns         => __('messages.navigation_groups.campaigns'),
            self::News              => __('messages.navigation_groups.news'),
            self::Referral          => __('messages.navigation_groups),
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::UserManagement    => 'heroicon-o-users',
            self::ContentManagement => 'heroicon-o-document-text',
            self::Ecommerce         => 'heroicon-o-shopping-cart',
            self::System            => 'heroicon-o-cog-6-tooth',
            self::Analytics         => 'heroicon-o-chart-bar',
            self::Marketing         => 'heroicon-o-megaphone',
            self::Reports           => 'heroicon-o-document-chart-bar',
            self::Settings          => 'heroicon-o-adjustments-horizontal',
            self::Users             => 'heroicon-o-users',
            self::Products          => 'heroicon-o-cube',
            self::Orders            => 'heroicon-o-shopping-bag',
            self::Inventory         => 'heroicon-o-archive-box',
            self::Content           => 'heroicon-o-document-text',
            self::Locations         => 'heroicon-o-globe-alt',
            self::Discounts         => 'heroicon-o-tag',
            self::Campaigns         => 'heroicon-o-megaphone',
            self::News              => 'heroicon-o-newspaper',
            self::Referral          => 'heroicon-o-gift',
        };
    }

    public function icon(): string
    {
        return str_replace('heroicon-o-', '', $this->getIcon());
    }

    public function priority(): int
    {
        return match ($this) {
            self::UserManagement    => 10,
            self::ContentManagement => 20,
            self::Ecommerce         => 30,
            self::System            => 90,
            self::Analytics         => 40,
            self::Marketing         => 50,
            self::Reports           => 60,
            self::Settings          => 80,
            self::Users             => 11,
            self::Products          => 31,
            self::Orders            => 32,
            self::Inventory         => 33,
            self::Content           => 21,
            self::Locations         => 34,
            self::Discounts         => 35,
            self::Campaigns         => 51,
            self::News              => 22,
            self::Referral          => 52,
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::UserManagement    => 'blue',
            self::ContentManagement => 'green',
            self::Ecommerce         => 'orange',
            self::System            => 'gray',
            self::Analytics         => 'purple',
            self::Marketing         => 'pink',
            self::Reports           => 'indigo',
            self::Settings          => 'slate',
            self::Users             => 'blue',
            self::Products          => 'blue',
            self::Orders            => 'green',
            self::Inventory         => 'teal',
            self::Content           => 'green',
            self::Locations         => 'emerald',
            self::Discounts         => 'rose',
            self::Campaigns         => 'orange',
            self::News              => 'blue',
            self::Referral          => 'purple',
        };
    }
}
