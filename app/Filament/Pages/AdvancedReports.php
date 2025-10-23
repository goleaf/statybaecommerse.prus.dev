<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;
use UnitEnum;
final class AdvancedReports extends Page
{
    /**
     * @var string|\BackedEnum|null
     */
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    /**
     * @return string|UnitEnum|null
     */
    public static function getNavigationGroup(): ?string
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
