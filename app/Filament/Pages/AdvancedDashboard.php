<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\AdvancedStatsWidget;
use App\Filament\Widgets\ComprehensiveStatsWidget;
use App\Filament\Widgets\EnhancedEcommerceOverview;
use App\Filament\Widgets\RealtimeAnalyticsWidget;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\TopProductsWidget;
use App\Filament\Widgets\TopSellingProductsWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use BackedEnum;

final class AdvancedDashboard extends BaseDashboard
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = null;

    protected static ?int $navigationSort = 1;

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
            EnhancedEcommerceOverview::class,
            AdvancedStatsWidget::class,
            RealtimeAnalyticsWidget::class,
            SalesChart::class,
            RecentOrdersWidget::class,
            TopSellingProductsWidget::class,
            TopProductsWidget::class,
        ];
    }

    public function getColumns(): int|array
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
            EnhancedEcommerceOverview::class,
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            RealtimeAnalyticsWidget::class,
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_dashboard') ?? true;
    }
}
