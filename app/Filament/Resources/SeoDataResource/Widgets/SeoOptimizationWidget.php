<?php

declare (strict_types=1);
namespace App\Filament\Resources\SeoDataResource\Widgets;

use App\Models\SeoData;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
/**
 * SeoOptimizationWidget
 * 
 * Filament v4 resource for SeoOptimizationWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class SeoOptimizationWidget extends BaseWidget
{
    protected static ?string $heading = 'SEO Optimization Status';
    protected static ?int $sort = 4;
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $totalSeoData = SeoData::count();
        if ($totalSeoData === 0) {
            return [Stat::make(__('admin.seo_data.widgets.no_data'), 0)->description(__('admin.seo_data.widgets.no_data_description'))->descriptionIcon('heroicon-m-information-circle')->color('gray')];
        }
        $withTitle = SeoData::withTitle()->count();
        $withDescription = SeoData::withDescription()->count();
        $withKeywords = SeoData::withKeywords()->count();
        $withCanonicalUrl = SeoData::withCanonicalUrl()->count();
        $withStructuredData = SeoData::withStructuredData()->count();
        $titlePercentage = round($withTitle / $totalSeoData * 100, 1);
        $descriptionPercentage = round($withDescription / $totalSeoData * 100, 1);
        $keywordsPercentage = round($withKeywords / $totalSeoData * 100, 1);
        $canonicalPercentage = round($withCanonicalUrl / $totalSeoData * 100, 1);
        $structuredDataPercentage = round($withStructuredData / $totalSeoData * 100, 1);
        return [Stat::make(__('admin.seo_data.fields.title'), $withTitle)->description($titlePercentage . '% ' . __('admin.seo_data.widgets.of_total'))->descriptionIcon('heroicon-m-document-text')->color($titlePercentage >= 90 ? 'success' : ($titlePercentage >= 70 ? 'warning' : 'danger')), Stat::make(__('admin.seo_data.fields.description'), $withDescription)->description($descriptionPercentage . '% ' . __('admin.seo_data.widgets.of_total'))->descriptionIcon('heroicon-m-document-text')->color($descriptionPercentage >= 90 ? 'success' : ($descriptionPercentage >= 70 ? 'warning' : 'danger')), Stat::make(__('admin.seo_data.fields.keywords'), $withKeywords)->description($keywordsPercentage . '% ' . __('admin.seo_data.widgets.of_total'))->descriptionIcon('heroicon-m-tag')->color($keywordsPercentage >= 90 ? 'success' : ($keywordsPercentage >= 70 ? 'warning' : 'danger')), Stat::make(__('admin.seo_data.fields.canonical_url'), $withCanonicalUrl)->description($canonicalPercentage . '% ' . __('admin.seo_data.widgets.of_total'))->descriptionIcon('heroicon-m-link')->color($canonicalPercentage >= 90 ? 'success' : ($canonicalPercentage >= 70 ? 'warning' : 'danger')), Stat::make(__('admin.seo_data.fields.structured_data'), $withStructuredData)->description($structuredDataPercentage . '% ' . __('admin.seo_data.widgets.of_total'))->descriptionIcon('heroicon-m-code-bracket')->color($structuredDataPercentage >= 90 ? 'success' : ($structuredDataPercentage >= 70 ? 'warning' : 'danger')), Stat::make(__('admin.seo_data.widgets.complete_seo'), SeoData::whereNotNull('title')->whereNotNull('description')->whereNotNull('keywords')->whereNotNull('canonical_url')->whereNotNull('structured_data')->count())->description(round(SeoData::whereNotNull('title')->whereNotNull('description')->whereNotNull('keywords')->whereNotNull('canonical_url')->whereNotNull('structured_data')->count() / $totalSeoData * 100, 1) . '% ' . __('admin.seo_data.widgets.of_total'))->descriptionIcon('heroicon-m-check-circle')->color('success')];
    }
}