<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;

final class AdvancedReports extends Page
{
    /**
     * @var string|BackedEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?string $title = 'Advanced Reports';

    protected static ?string $slug = 'advanced-reports';

    protected string $view = 'filament.pages.advanced-reports';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
