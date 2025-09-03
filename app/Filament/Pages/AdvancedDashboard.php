<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\AdvancedStatsWidget;
use App\Filament\Widgets\ComprehensiveStatsWidget;
use App\Filament\Widgets\LatestOrdersWidget;
use App\Filament\Widgets\OrdersChartWidget;
use App\Filament\Widgets\TopSellingProductsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

final class AdvancedDashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static ?string $navigationLabel = null;
    
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament-panels::pages.dashboard';

    public function getTitle(): string
    {
        return __('Advanced Dashboard');
    }

    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
    }

    public function getWidgets(): array
    {
        return [
            ComprehensiveStatsWidget::class,
            AdvancedStatsWidget::class,
            OrdersChartWidget::class,
            LatestOrdersWidget::class,
            TopSellingProductsWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 4,
            '2xl' => 6,
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            ComprehensiveStatsWidget::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            AdvancedStatsWidget::class,
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_dashboard_stats') ?? false;
    }
}
