<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

final class SliderAnalyticsTest extends BaseDashboard
{
    protected static ?string $title = 'Slider Analytics Test';

    protected static ?string $navigationLabel = 'Slider Analytics Test';

    /**
     * @var string|\BackedEnum|null
     */
    public static function getNavigationIcon(): \BackedEnum|\Illuminate\Contracts\Support\Htmlable|string|null
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
