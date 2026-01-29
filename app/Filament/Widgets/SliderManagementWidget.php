<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Slider;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

final class SliderManagementWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalSliders = Slider::count();
        $activeSliders = Slider::where('is_active', true)->count();
        $inactiveSliders = Slider::where('is_active', false)->count();
        $recentSliders = Slider::where('created_at', '>=', Carbon::now()->subDays(7))->count();

        return [
            Stat::make(__('translations.total_sliders'), \Illuminate\Support\Number::format($totalSliders))
                ->description(__('translations.all_sliders'))
                ->color('primary'),
            Stat::make(__('translations.active_sliders'), \Illuminate\Support\Number::format($activeSliders))
                ->description(__('translations.currently_active'))
                ->color('success'),
            Stat::make(__('translations.inactive_sliders'), \Illuminate\Support\Number::format($inactiveSliders))
                ->description(__('translations.currently_inactive'))
                ->color('danger'),
            Stat::make(__('translations.recent_sliders'), \Illuminate\Support\Number::format($recentSliders))
                ->description(__('translations.added_this_week'))
                ->color('info'),
        ];
    }
}
