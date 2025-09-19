<?php declare(strict_types=1);

namespace App\Filament\Pages\SliderAnalytics\Widgets;

use App\Models\Slider;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class SliderOverviewStats extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public function getStats(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->subDays(30);
        $endDate = $this->pageFilters['endDate'] ?? now();
        $sliderId = $this->pageFilters['sliderId'] ?? null;
        $status = $this->pageFilters['status'] ?? 'all';

        $query = Slider::query()
            ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
            ->when($sliderId, fn (Builder $query) => $query->where('id', $sliderId))
            ->when($status !== 'all', fn (Builder $query) => $query->where('is_active', $status === 'active'));

        $totalSliders = $query->count();
        $activeSliders = $query->where('is_active', true)->count();
        $inactiveSliders = $query->where('is_active', false)->count();
        $slidersWithImages = $query->whereHas('media', fn (Builder $q) => $q->where('collection_name', 'slider_images'))->count();
        $slidersWithBackgrounds = $query->whereHas('media', fn (Builder $q) => $q->where('collection_name', 'slider_backgrounds'))->count();
        $recentSliders = $query->where('created_at', '>=', now()->subDays(7))->count();

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
