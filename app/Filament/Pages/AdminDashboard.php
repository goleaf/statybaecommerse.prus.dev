<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class AdminDashboard extends BaseDashboard
{
    /**
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
