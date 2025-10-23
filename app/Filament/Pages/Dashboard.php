<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Gate;

class Dashboard extends BaseDashboard
{
    protected static ?int $navigationSort = 1;

    /** @var string|\BackedEnum|null */
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard';

    public static function getNavigationLabel(): string
    {
        return trans('admin.navigation.dashboard');
    }

    public function getTitle(): string
    {
        return trans('admin.navigation.dashboard');
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\DashboardKpiWidget::class,
            \App\Filament\Widgets\DashboardTimeSeriesWidget::class,
            \App\Filament\Widgets\DashboardRecentOrdersTable::class,
            \App\Filament\Widgets\DashboardLowStockTable::class,
            \App\Filament\Widgets\DashboardRecentErrorsTable::class,
            \App\Filament\Widgets\DashboardQuickActionsWidget::class,
            \App\Filament\Widgets\CalendarWidget::class,
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

    /**
     * Allow dashboard access by default while still respecting optional permission configuration.
     */
    public static function canAccess(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $abilities = array_values((array) config('dashboard.permissions'));

        return Gate::any($abilities) || (bool) auth()->user()?->is_admin;
    }
}
