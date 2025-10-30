<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminStatsWidget;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class AdminDashboard extends BaseDashboard
{
    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations while
     * retaining compatibility with Filament's expected PHPDoc union typing convention.
     */
//    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home';
    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
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
