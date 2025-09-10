<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum NavigationGroup: string implements HasLabel, HasIcon
{
    case Dashboard = 'dashboard';
    case Catalog = 'catalog';
    case Orders = 'orders';
    case Customers = 'customers';
    case Marketing = 'marketing';
    case Partners = 'partners';
    case Content = 'content';
    case Documents = 'documents';
    case Analytics = 'analytics';
    case Inventory = 'inventory';
    case Settings = 'settings';
    case System = 'system';
    case Reports = 'reports';

    public function getLabel(): string
    {
        return match ($this) {
            self::Dashboard => __('navigation.groups.dashboard'),
            self::Catalog => __('navigation.groups.catalog'),
            self::Orders => __('navigation.groups.orders'),
            self::Customers => __('navigation.groups.customers'),
            self::Marketing => __('navigation.groups.marketing'),
            self::Partners => __('navigation.groups.partners'),
            self::Content => __('navigation.groups.content'),
            self::Documents => __('navigation.groups.documents'),
            self::Analytics => __('navigation.groups.analytics'),
            self::Inventory => __('navigation.groups.inventory'),
            self::Settings => __('navigation.groups.settings'),
            self::System => __('navigation.groups.system'),
            self::Reports => __('navigation.groups.reports'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Dashboard => 'heroicon-o-home',
            self::Catalog => 'heroicon-o-cube',
            self::Orders => 'heroicon-o-shopping-bag',
            self::Customers => 'heroicon-o-users',
            self::Marketing => 'heroicon-o-megaphone',
            self::Partners => 'heroicon-o-building-office',
            self::Content => 'heroicon-o-document-text',
            self::Documents => 'heroicon-o-document-duplicate',
            self::Analytics => 'heroicon-o-chart-bar',
            self::Inventory => 'heroicon-o-cube-transparent',
            self::Settings => 'heroicon-o-cog-6-tooth',
            self::System => 'heroicon-o-server',
            self::Reports => 'heroicon-o-chart-bar',
        };
    }
}
