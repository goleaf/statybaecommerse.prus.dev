<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

final class AdvancedReports extends Page
{
    /**
     * Navigation icon that Filament displays for this page.
     *
     * @var string|BackedEnum|null Filament navigation icon identifier.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static \UnitEnum|string|null $navigationGroup = 'Analytics';

    protected static ?string $title = 'Advanced Reports';

    protected static ?string $slug = 'advanced-reports';

    protected string $view = 'filament.pages.advanced-reports';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
