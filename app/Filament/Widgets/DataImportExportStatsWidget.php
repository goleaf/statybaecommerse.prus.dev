<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

final class DataImportExportStatsWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        return [
            Stat::make(__('messages.admin_products'), Number::format(Product::count())),
            Stat::make(__('messages.admin_categories'), Number::format(Category::count())),
            Stat::make(__('messages.admin_brands'), Number::format(Brand::count())),
            Stat::make(__('messages.users'), Number::format(User::count())),
            Stat::make(__('messages.admin_orders'), Number::format(Order::count())),
        ];
    }
}
