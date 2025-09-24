<?php declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

final class AdvancedReports extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static UnitEnum|string|null $navigationGroup = 'Analytics';

    protected static ?string $title = 'Advanced Reports';

    protected static ?string $slug = 'advanced-reports';

    protected string $view = 'filament.pages.advanced-reports';
}
