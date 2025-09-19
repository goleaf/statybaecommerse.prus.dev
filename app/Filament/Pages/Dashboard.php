<?php declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use BackedEnum;
use UnitEnum;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Dashboard';

    public function getTitle(): string
    {
        return __('admin.navigation.dashboard');
    }

    public function getWidgets(): array
    {
        return [
            // Simplified Comprehensive Statistics Widget
            \App\Filament\Widgets\SimplifiedStatsWidget::class,
            // Comprehensive Analytics Dashboard
            \App\Filament\Widgets\ComprehensiveAnalyticsWidget::class,
            // Recent Activity Dashboard
            \App\Filament\Widgets\RecentActivityWidget::class,
            // Existing Comprehensive Statistics Widgets
            \App\Filament\Widgets\DashboardOverviewWidget::class,
            \App\Filament\Widgets\ComprehensiveStatsWidget::class,
            \App\Filament\Widgets\EcommerceStatsWidget::class,
            // Advanced Analytics and Charts
            \App\Filament\Widgets\AdvancedAnalyticsWidget::class,
            \App\Filament\Widgets\OrdersChartWidget::class,
            \App\Filament\Widgets\VariantPerformanceChart::class,
            \App\Filament\Widgets\CampaignPerformanceWidget::class,
            // Recent Activity Widgets
            \App\Filament\Widgets\RecentOrdersWidget::class,
            \App\Filament\Widgets\LatestOrdersWidget::class,
            \App\Filament\Widgets\RecentSlidersWidget::class,
            // Management Widgets
            \App\Filament\Widgets\SliderQuickActionsWidget::class,
            \App\Filament\Widgets\SliderManagementWidget::class,
            // Analytics and Performance Widgets
            \App\Filament\Widgets\VariantAnalyticsWidget::class,
            \App\Filament\Widgets\VariantStockWidget::class,
            \App\Filament\Widgets\VariantPriceWidget::class,
            // System and Settings Widgets
            \App\Filament\Widgets\SystemSettingsOverviewWidget::class,
            \App\Filament\Widgets\SystemSettingsByTypeWidget::class,
            \App\Filament\Widgets\SystemSettingsByCategoryWidget::class,
            // Translation Management Widgets
            \App\Filament\Widgets\MasterMultilanguageTabsWidget::class,
            \App\Filament\Widgets\ProductTranslationTabsWidget::class,
            \App\Filament\Widgets\CategoryTranslationTabsWidget::class,
        ];
    }

    public function getColumns(): array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 4,
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_dashboard') ?? false;
    }
}
