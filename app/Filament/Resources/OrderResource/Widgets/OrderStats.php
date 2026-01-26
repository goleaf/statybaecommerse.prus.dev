<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Orders', Order::count()),
            Stat::make('Pending Orders', Order::where('status', OrderStatus::PENDING)->count()),
            Stat::make('Revenue (Today)', number_format(Order::createdToday()->sum('total'), 2) . ' €'),
        ];
    }
}
