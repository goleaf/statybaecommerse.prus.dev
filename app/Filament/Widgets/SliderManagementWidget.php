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
            Stat::make(__('messages.translations), \Illuminate\Support\Number::format($totalSliders))
                ->description(__('messages.translations))
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),
            Stat::make(__('messages.translations), \Illuminate\Support\Number::format($activeSliders))
                ->description(__('messages.translations))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make(__('messages.translations), \Illuminate\Support\Number::format($inactiveSliders))
                ->description(__('messages.translations))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
            Stat::make(__('messages.translations), \Illuminate\Support\Number::format($recentSliders))
                ->description(__('messages.translations))
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
        ];
    }
}
