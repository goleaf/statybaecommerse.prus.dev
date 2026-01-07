<?php

declare(strict_types=1);

namespace App\Filament\Resources\SeoDataResource\Widgets;

use App\Models\SeoData;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class SeoDataOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalSeoData = SeoData::count();
        $activeSeoData = SeoData::where('is_active', true)->count();
        $indexedSeoData = SeoData::where('is_indexed', true)->count();
        $canonicalSeoData = SeoData::where('is_canonical', true)->count();

        return [
            Stat::make(__('seo_data.stats.total_seo_data'), $totalSeoData)
                ->description(__('seo_data.stats.total_seo_data_description'))
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('primary'),

            Stat::make(__('seo_data.stats.active_seo_data'), $activeSeoData)
                ->description(__('seo_data.stats.active_seo_data_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('seo_data.stats.indexed_seo_data'), $indexedSeoData)
                ->description(__('seo_data.stats.indexed_seo_data_description'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make(__('seo_data.stats.canonical_seo_data'), $canonicalSeoData)
                ->description(__('seo_data.stats.canonical_seo_data_description'))
                ->descriptionIcon('heroicon-m-link')
                ->color('warning'),
        ];
    }
}
