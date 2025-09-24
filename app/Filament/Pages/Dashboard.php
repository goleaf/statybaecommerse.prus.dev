<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return 'heroicon-o-home';
    }

    public function getTitle(): string
    {
        return __('admin.navigation.dashboard');
    }

    public function getWidgets(): array
    {
        return [
            // Simplified Comprehensive Statistics Widget
            \App\Filament\Widgets\SimplifiedStatsWidget::class,
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
        return true;  // Temporarily allow access for testing
    }
}
