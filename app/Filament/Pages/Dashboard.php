<?php declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use BackedEnum;
use UnitEnum;

class Dashboard extends BaseDashboard
{
    // /** @var BackedEnum|string|null */
    // protected static $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Dashboard';

    public function getTitle(): string
    {
        return __('admin.navigation.dashboard');
    }

    public function getWidgets(): array
    {
        return [
            // Dashboard Overview - Key Metrics
            \App\Filament\Widgets\DashboardOverviewWidget::class,
            // Advanced Analytics Charts
            \App\Filament\Widgets\AdvancedAnalyticsWidget::class,
            // Revenue Analytics
            \App\Filament\Widgets\RevenueAnalyticsWidget::class,
            // Product Performance
            \App\Filament\Widgets\ProductPerformanceWidget::class,
            // User Activity Analytics
            \App\Filament\Widgets\UserActivityWidget::class,
            // Campaign Performance
            \App\Filament\Widgets\CampaignPerformanceWidget::class,
            // Inventory Analytics
            \App\Filament\Widgets\InventoryAnalyticsWidget::class,
            // System Performance
            \App\Filament\Widgets\SystemPerformanceWidget::class,
            // Real-time Analytics
            \App\Filament\Widgets\RealtimeAnalyticsWidget::class,
            // Recent Activity Feed
            \App\Filament\Widgets\RecentActivityWidget::class,
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
