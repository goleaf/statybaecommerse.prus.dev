<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Campaign;
use App\Models\EnumValue;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('admin.dashboard.stats.total_orders'), Order::count())
                ->description(__('admin.dashboard.stats.orders_description'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make(__('admin.dashboard.stats.total_products'), Product::count())
                ->description(__('admin.dashboard.stats.products_description'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
            Stat::make(__('admin.dashboard.stats.total_users'), User::count())
                ->description(__('admin.dashboard.stats.users_description'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
            Stat::make(__('admin.dashboard.stats.active_campaigns'), Campaign::where('is_active', true)->count())
                ->description(__('admin.dashboard.stats.campaigns_description'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger'),
            Stat::make(__('admin.dashboard.stats.enum_values'), EnumValue::count())
                ->description(__('admin.dashboard.stats.enum_values_description'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('gray'),
        ];
    }
}
