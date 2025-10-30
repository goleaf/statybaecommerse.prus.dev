<?php

declare(strict_types=1);

namespace App\Filament\Pages\SliderAnalytics\Widgets;

use App\Filament\Pages\SliderAnalytics\Concerns\ResolvesPageFilters;
use App\Models\Slider;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

final class SliderClickThroughRates extends BaseWidget
{
    use InteractsWithPageFilters;
    use ResolvesPageFilters;

    protected ?string $heading = 'Click-Through Rate Analysis';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    public function getStats(): array
    {
        // Share the same filter resolution path so CTR metrics reflect the
        // precise slider cohort and time range selected on the page.
        [$startDate, $endDate] = $this->resolveDateRange();
        $sliderId = $this->resolveFilterValue('sliderId');
        $status = $this->resolveFilterValue('status', 'all');

        $query = Slider::query()
            ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
            ->when($sliderId, fn (Builder $query) => $query->where('id', $sliderId))
            ->when($status !== 'all', fn (Builder $query) => $query->where('is_active', $status === 'active'));

        // Each metric uses a cloned builder to avoid stacking button-specific
        // constraints on subsequent calculations.
        $totalSliders = (clone $query)->count();
        $slidersWithButtons = (clone $query)
            ->whereNotNull('button_text')
            ->whereNotNull('button_url')
            ->where('button_text', '!=', '')
            ->where('button_url', '!=', '')
            ->count();

        $slidersWithExternalLinks = (clone $query)
            ->whereNotNull('button_url')
            ->where('button_url', 'like', 'http%')
            ->count();

        $slidersWithInternalLinks = (clone $query)
            ->whereNotNull('button_url')
            ->where('button_url', 'not like', 'http%')
            ->count();

        // Calculate CTR metrics (simulated data for demonstration)
        $avgCTR = $totalSliders > 0 ? round(($slidersWithButtons / $totalSliders) * 100, 1) : 0;
        $externalCTR = $slidersWithExternalLinks > 0 ? round(($slidersWithExternalLinks / $totalSliders) * 100, 1) : 0;
        $internalCTR = $slidersWithInternalLinks > 0 ? round(($slidersWithInternalLinks / $totalSliders) * 100, 1) : 0;

        return [
            Stat::make('Average CTR', $avgCTR . '%')
                ->description('Overall click-through rate')
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('primary'),
            Stat::make('External Links', $slidersWithExternalLinks)
                ->description($externalCTR . '% of sliders')
                ->descriptionIcon('heroicon-m-arrow-top-right-on-square')
                ->color('success'),
            Stat::make('Internal Links', $slidersWithInternalLinks)
                ->description($internalCTR . '% of sliders')
                ->descriptionIcon('heroicon-m-arrow-right')
                ->color('info'),
            Stat::make('No Buttons', $totalSliders - $slidersWithButtons)
                ->description('Sliders without buttons')
                ->descriptionIcon('heroicon-m-x-mark')
                ->color('gray'),
        ];
    }
}
