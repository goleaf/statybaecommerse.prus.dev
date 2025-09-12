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
        $totalProducts = SeoData::forProducts()->count();
        $totalCategories = SeoData::forCategories()->count();
        $totalBrands = SeoData::forBrands()->count();

        $lithuanianSeo = SeoData::forLocale('lt')->count();
        $englishSeo = SeoData::forLocale('en')->count();

        $withTitle = SeoData::withTitle()->count();
        $withDescription = SeoData::withDescription()->count();
        $withKeywords = SeoData::withKeywords()->count();
        $withCanonicalUrl = SeoData::withCanonicalUrl()->count();
        $withStructuredData = SeoData::withStructuredData()->count();

        $noIndexCount = SeoData::where('no_index', true)->count();
        $noFollowCount = SeoData::where('no_follow', true)->count();

        // Calculate average SEO score
        $avgSeoScore = SeoData::selectRaw('
            AVG(
                CASE WHEN title IS NOT NULL THEN 20 ELSE 0 END +
                CASE WHEN title IS NOT NULL AND LENGTH(title) BETWEEN 30 AND 60 THEN 20 ELSE 0 END +
                CASE WHEN description IS NOT NULL THEN 15 ELSE 0 END +
                CASE WHEN description IS NOT NULL AND LENGTH(description) BETWEEN 120 AND 160 THEN 15 ELSE 0 END +
                CASE WHEN keywords IS NOT NULL THEN 10 ELSE 0 END +
                CASE WHEN keywords IS NOT NULL AND LENGTH(keywords) - LENGTH(REPLACE(keywords, ",", "")) + 1 BETWEEN 3 AND 10 THEN 5 ELSE 0 END +
                CASE WHEN canonical_url IS NOT NULL THEN 10 ELSE 0 END +
                CASE WHEN structured_data IS NOT NULL THEN 5 ELSE 0 END
            ) as avg_score
        ')->value('avg_score') ?? 0;

        return [
            Stat::make(__('admin.seo_data.widgets.total_seo_data'), $totalSeoData)
                ->description(__('admin.seo_data.widgets.total_seo_data_description'))
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('primary'),

            Stat::make(__('admin.models.products'), $totalProducts)
                ->description(__('admin.seo_data.widgets.products_with_seo'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make(__('admin.models.categories'), $totalCategories)
                ->description(__('admin.seo_data.widgets.categories_with_seo'))
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),

            Stat::make(__('admin.models.brands'), $totalBrands)
                ->description(__('admin.seo_data.widgets.brands_with_seo'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('warning'),

            Stat::make(__('admin.seo_data.widgets.lithuanian_seo'), $lithuanianSeo)
                ->description(__('admin.seo_data.widgets.lithuanian_seo_description'))
                ->descriptionIcon('heroicon-m-language')
                ->color('primary'),

            Stat::make(__('admin.seo_data.widgets.english_seo'), $englishSeo)
                ->description(__('admin.seo_data.widgets.english_seo_description'))
                ->descriptionIcon('heroicon-m-language')
                ->color('secondary'),

            Stat::make(__('admin.seo_data.widgets.avg_seo_score'), number_format($avgSeoScore, 1).'/100')
                ->description(__('admin.seo_data.widgets.avg_seo_score_description'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($avgSeoScore >= 80 ? 'success' : ($avgSeoScore >= 60 ? 'warning' : 'danger')),

            Stat::make(__('admin.seo_data.widgets.complete_seo'),
                SeoData::whereNotNull('title')
                    ->whereNotNull('description')
                    ->whereNotNull('keywords')
                    ->whereNotNull('canonical_url')
                    ->whereNotNull('structured_data')
                    ->count()
            )
                ->description(__('admin.seo_data.widgets.complete_seo_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('admin.seo_data.widgets.needs_optimization'),
                SeoData::where(function ($query) {
                    $query->whereNull('title')
                        ->orWhereNull('description')
                        ->orWhereNull('keywords')
                        ->orWhereNull('canonical_url')
                        ->orWhereNull('structured_data');
                })->count()
            )
                ->description(__('admin.seo_data.widgets.needs_optimization_description'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make(__('admin.seo_data.widgets.no_index_pages'), $noIndexCount)
                ->description(__('admin.seo_data.widgets.no_index_pages_description'))
                ->descriptionIcon('heroicon-m-x-mark')
                ->color('danger'),

            Stat::make(__('admin.seo_data.widgets.no_follow_pages'), $noFollowCount)
                ->description(__('admin.seo_data.widgets.no_follow_pages_description'))
                ->descriptionIcon('heroicon-m-x-mark')
                ->color('danger'),
        ];
    }
}
