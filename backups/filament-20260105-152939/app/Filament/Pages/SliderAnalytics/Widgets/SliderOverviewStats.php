<?php

declare(strict_types=1);

namespace App\Filament\Pages\SliderAnalytics\Widgets;

use App\Filament\Pages\SliderAnalytics\Concerns\ResolvesPageFilters;
use App\Models\Slider;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

final class SliderOverviewStats extends BaseWidget
{
    use InteractsWithPageFilters;
    use ResolvesPageFilters;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function getStats(): array
    {
        // Normalise the filter payload so stats use the same window and scopes as
        // the surrounding widgets even before the modal has been opened.
        [$startDate, $endDate] = $this->resolveDateRange();
        $sliderId = $this->resolveFilterValue('sliderId');
        $status = $this->resolveFilterValue('status', 'all');

        $query = Slider::query()
            ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
            ->when($sliderId, fn (Builder $query) => $query->where('id', $sliderId))
            ->when($status !== 'all', fn (Builder $query) => $query->where('is_active', $status === 'active'));

        // Clone the base builder for each metric so cumulative where clauses do
        // not leak between counts when the widget rehydrates.
        $totalSliders = (clone $query)->count();
        $activeSliders = (clone $query)->where('is_active', true)->count();
        $inactiveSliders = (clone $query)->where('is_active', false)->count();
        $slidersWithImages = (clone $query)
            ->whereHas('media', fn (Builder $mediaQuery) => $mediaQuery->where('collection_name', 'slider_images'))
            ->count();
        $slidersWithBackgrounds = (clone $query)
            ->whereHas('media', fn (Builder $mediaQuery) => $mediaQuery->where('collection_name', 'slider_backgrounds'))
            ->count();
        $recentSliders = (clone $query)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return [
            Stat::make('Total Sliders', $totalSliders)
                ->description('All sliders in period')
                ->descriptionIcon('heroicon-m-photo')
                ->color('primary'),
            Stat::make('Active Sliders', $activeSliders)
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),
            Stat::make('Inactive Sliders', $inactiveSliders)
                ->description('Currently inactive')
                ->descriptionIcon('heroicon-m-eye-slash')
                ->color('gray'),
            Stat::make('With Images', $slidersWithImages)
                ->description('Sliders with images')
                ->descriptionIcon('heroicon-m-camera')
                ->color('info'),
            Stat::make('With Backgrounds', $slidersWithBackgrounds)
                ->description('Sliders with backgrounds')
                ->descriptionIcon('heroicon-m-paint-brush')
                ->color('warning'),
            Stat::make('Recent Sliders', $recentSliders)
                ->description('Created in last 7 days')
                ->descriptionIcon('heroicon-m-clock')
                ->color('success'),
        ];
    }
}
