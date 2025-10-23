<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

final class AdvancedReports extends Page
{
    /**
     * @var string|BackedEnum|null Navigation icon override documented for enum compatibility.
     */
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    public static function getNavigationGroup(): BackedEnum|string|null
    {
        return 'Analytics'; // Ensure advanced analytics live with the rest of the reporting suite.
    }

    protected static ?string $title = 'Advanced Reports';

    protected static ?string $slug = 'advanced-reports';

    protected string $view = 'filament.pages.advanced-reports';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
