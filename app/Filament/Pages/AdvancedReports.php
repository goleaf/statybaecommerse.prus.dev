<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;
use Filament\Pages\Page;
use UnitEnum;

final class AdvancedReports extends Page
{
    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return 'heroicon-o-chart-bar-square';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Analytics';
    }

    protected static ?string $title = 'Advanced Reports';

    protected static ?string $slug = 'advanced-reports';

    protected string $view = 'filament.pages.advanced-reports';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
