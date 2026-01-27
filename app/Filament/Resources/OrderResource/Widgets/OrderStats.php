<?php

declare(strict_types=1);

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
            Stat::make(__('messages.admin.widgets.total_orders'), Order::count()),
            Stat::make(__('messages.pending_orders'), Order::where('status', OrderStatus::PENDING)->count()),
            Stat::make(__('messages.todays_revenue'), number_format(Order::createdToday()->sum('total'), 2) . ' €'),
        ];
    }
}
