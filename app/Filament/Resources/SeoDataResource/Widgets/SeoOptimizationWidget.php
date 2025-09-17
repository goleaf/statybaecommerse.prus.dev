<?php

declare(strict_types=1);

namespace App\Filament\Resources\SeoDataResource\Widgets;

use App\Models\SeoData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class SeoOptimizationWidget extends ChartWidget
{
    protected ?string $heading = 'SEO Optimization Status';

    protected function getData(): array
    {
        $totalSeoData = SeoData::count();
        $optimizedSeoData = SeoData::where('is_active', true)
            ->where('is_indexed', true)
            ->whereNotNull('title')
            ->whereNotNull('description')
            ->whereNotNull('keywords')
            ->count();
        
        $needsOptimization = $totalSeoData - $optimizedSeoData;

        return [
            'datasets' => [
                [
                    'label' => __('seo_data.charts.optimization_status'),
                    'data' => [$optimizedSeoData, $needsOptimization],
                    'backgroundColor' => [
                        '#10B981', // emerald (optimized)
                        '#F59E0B', // amber (needs optimization)
                    ],
                ],
            ],
            'labels' => [
                __('seo_data.charts.optimized'),
                __('seo_data.charts.needs_optimization'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
