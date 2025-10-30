<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

final class SliderAnalyticsTest extends BaseDashboard
{
    protected static ?string $title = 'Slider Analytics Test';

    protected static ?string $navigationLabel = 'Slider Analytics Test';

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations while keeping
     * PHPStan-friendly union documentation for future contributors.
     */
//    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-chart-bar';
    }

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'slider-analytics-test';

    public function getTitle(): string
    {
        return 'Slider Analytics Test Dashboard';
    }

    public function getHeading(): string
    {
        return 'Slider Analytics Test';
    }

    public function getSubheading(): string
    {
        return 'This is a test page to verify that the SliderAnalytics page works';
    }
}
