<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\EcommerceStatsWidget;
use App\Filament\Widgets\LatestOrdersWidget;
use App\Filament\Widgets\OrdersChartWidget;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\TopSellingProductsWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use BackedEnum;
use UnitEnum;

final class Dashboard extends BaseDashboard
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    public function getTitle(): string
    {
        return __('Dashboard');
    }

    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
    }

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            EcommerceStatsWidget::class,
            OrdersChartWidget::class,
            LatestOrdersWidget::class,
            TopSellingProductsWidget::class,
            RecentOrdersWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }
}
