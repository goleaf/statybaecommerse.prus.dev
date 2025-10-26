<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class AdminDashboard extends BaseDashboard
{
    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations while
     * retaining compatibility with Filament's expected PHPDoc union typing convention.
     *
     * @var string|\BackedEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-home';

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
