<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

final class AdvancedReports extends Page
{
    /**
     * Navigation icon override (string|\BackedEnum|null).
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';

    public static function getNavigationGroup(): string|UnitEnum|null
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
