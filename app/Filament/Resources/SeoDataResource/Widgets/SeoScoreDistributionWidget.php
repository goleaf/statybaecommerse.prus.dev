<?php

declare(strict_types=1);

namespace App\Filament\Resources\SeoDataResource\Widgets;

use App\Models\SeoData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class SeoScoreDistributionWidget extends ChartWidget
{
    protected ?string $heading = 'SEO Score Distribution';

    protected function getData(): array
    {
        $excellent = SeoData::where('is_active', true)
            ->where('is_indexed', true)
            ->whereNotNull('title')
            ->whereNotNull('description')
            ->whereNotNull('keywords')
            ->whereNotNull('meta_title')
            ->whereNotNull('meta_description')
            ->count();

        $good = SeoData::where('is_active', true)
            ->where('is_indexed', true)
            ->where(function ($query) {
                $query->whereNotNull('title')
                    ->whereNotNull('description')
                    ->where(function ($q) {
                        $q->whereNull('keywords')
                            ->orWhereNull('meta_title')
                            ->orWhereNull('meta_description');
                    });
            })
            ->count();

        $needsImprovement = SeoData::where('is_active', true)
            ->where(function ($query) {
                $query->where('is_indexed', false)
                    ->orWhereNull('title')
                    ->orWhereNull('description');
            })
            ->count();

        $poor = SeoData::where('is_active', false)
            ->orWhere(function ($query) {
                $query->where('is_indexed', false)
                    ->whereNull('title')
                    ->whereNull('description');
            })
            ->count();

        return [
            'datasets' => [
                [
                    'label' => __('seo_data.charts.seo_score_distribution'),
                    'data' => [$excellent, $good, $needsImprovement, $poor],
                    'backgroundColor' => [
                        '#10B981', // emerald (excellent)
                        '#3B82F6', // blue (good)
                        '#F59E0B', // amber (needs improvement)
                        '#EF4444', // red (poor)
                    ],
                ],
            ],
            'labels' => [
                __('seo_data.charts.excellent'),
                __('seo_data.charts.good'),
                __('seo_data.charts.needs_improvement'),
                __('seo_data.charts.poor'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
