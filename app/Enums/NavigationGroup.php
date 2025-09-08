<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum NavigationGroup: string implements HasLabel, HasIcon
{
    case Catalog = 'catalog';
    case Orders = 'orders';
    case Customers = 'customers';
    case Marketing = 'marketing';
    case Content = 'content';
    case Settings = 'settings';
    case Reports = 'reports';

    public function getLabel(): string
    {
        return match ($this) {
            self::Catalog => __('navigation.groups.catalog'),
            self::Orders => __('navigation.groups.orders'),
            self::Customers => __('navigation.groups.customers'),
            self::Marketing => __('navigation.groups.marketing'),
            self::Content => __('navigation.groups.content'),
            self::Settings => __('navigation.groups.settings'),
            self::Reports => __('navigation.groups.reports'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Catalog => 'heroicon-o-cube',
            self::Orders => 'heroicon-o-shopping-bag',
            self::Customers => 'heroicon-o-users',
            self::Marketing => 'heroicon-o-megaphone',
            self::Content => 'heroicon-o-document-text',
            self::Settings => 'heroicon-o-cog-6-tooth',
            self::Reports => 'heroicon-o-chart-bar',
        };
    }
}
