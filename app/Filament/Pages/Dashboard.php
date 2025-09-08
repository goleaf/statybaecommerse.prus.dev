<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\EnhancedEcommerceOverview;
use App\Filament\Widgets\RealtimeAnalyticsWidget;
use App\Filament\Widgets\TopProductsWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use BackedEnum;

final class Dashboard extends BaseDashboard
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = null;

    protected static ?int $navigationSort = 1;

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
            EnhancedEcommerceOverview::class,
            RealtimeAnalyticsWidget::class,
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
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_dashboard') ?? true;
    }
}
