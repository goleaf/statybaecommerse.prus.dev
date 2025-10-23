<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class AdminDashboard extends BaseDashboard
{
    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return 'heroicon-o-home';
    }

    protected string $view = 'filament.pages.admin-dashboard';

    public function getWidgets(): array
    {
        return [
            AdminStatsWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
