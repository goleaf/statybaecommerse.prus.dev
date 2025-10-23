<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;

final class AdvancedReports extends Page
{
    /**
     * @var string|\BackedEnum|null
     */
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';

    /**
     * @return string|null
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
