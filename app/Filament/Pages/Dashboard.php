<?php declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

final class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament-panels::pages.dashboard';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverviewWidget::class,
            \App\Filament\Widgets\OrdersChartWidget::class,
            \App\Filament\Widgets\RecentOrdersWidget::class,
            \App\Filament\Widgets\TopSellingProductsWidget::class,
            \App\Filament\Widgets\LowStockProductsWidget::class,
            \App\Filament\Widgets\RevenueChartWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }
}
