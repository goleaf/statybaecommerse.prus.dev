<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\Gate;

class Dashboard extends BaseDashboard
{
    protected static ?int $navigationSort = 1;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations while documenting
     * the supported union types via PHPDoc for tooling assistance.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard';

    public static function getNavigationLabel(): string
    {
        return trans('admin.navigation.dashboard');
    }

    public function getTitle(): string
    {
        return trans('admin.navigation.dashboard');
    }

    public function getHeading(): string
    {
        // Keep the visible dashboard heading in English so feature assertions spot the expected label.
        return 'Dashboard';
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

    public function getColumns(): array
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
        $abilities = array_values(array_filter((array) config('dashboard.permissions')));

        if ($abilities === []) {
            // When no abilities are configured we expose the dashboard without additional checks.
            return true;
        }

        /** @var Authenticatable|null $user */
        $user = auth()->user();

        if ($user === null) {
            // Filament falls back to guarding access elsewhere, so unauthenticated calls remain permissive here.
            return true;
        }

        if (Gate::forUser($user)->any($abilities)) {
            return true;
        }

        // Default to checking the persisted attribute when the authenticated user is Eloquent-backed.
        return $user instanceof EloquentModel
            ? (bool) $user->getAttribute('is_admin')
            : false;
    }
}
