<?php

declare (strict_types=1);
namespace App\Filament\Resources\SeoDataResource\Widgets;

use App\Models\SeoData;
use Filament\Widgets\ChartWidget;
/**
 * SeoScoreDistributionWidget
 * 
 * Filament v4 resource for SeoScoreDistributionWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class SeoScoreDistributionWidget extends ChartWidget
{
    protected static ?string $heading = 'SEO Score Distribution';
    protected static ?int $sort = 2;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $scores = SeoData::selectRaw('
            CASE 
                WHEN (
                    CASE WHEN title IS NOT NULL THEN 20 ELSE 0 END +
                    CASE WHEN title IS NOT NULL AND LENGTH(title) BETWEEN 30 AND 60 THEN 20 ELSE 0 END +
                    CASE WHEN description IS NOT NULL THEN 15 ELSE 0 END +
                    CASE WHEN description IS NOT NULL AND LENGTH(description) BETWEEN 120 AND 160 THEN 15 ELSE 0 END +
                    CASE WHEN keywords IS NOT NULL THEN 10 ELSE 0 END +
                    CASE WHEN keywords IS NOT NULL AND LENGTH(keywords) - LENGTH(REPLACE(keywords, ",", "")) + 1 BETWEEN 3 AND 10 THEN 5 ELSE 0 END +
                    CASE WHEN canonical_url IS NOT NULL THEN 10 ELSE 0 END +
                    CASE WHEN structured_data IS NOT NULL THEN 5 ELSE 0 END
                ) >= 80 THEN "Excellent (80-100)"
                WHEN (
                    CASE WHEN title IS NOT NULL THEN 20 ELSE 0 END +
                    CASE WHEN title IS NOT NULL AND LENGTH(title) BETWEEN 30 AND 60 THEN 20 ELSE 0 END +
                    CASE WHEN description IS NOT NULL THEN 15 ELSE 0 END +
                    CASE WHEN description IS NOT NULL AND LENGTH(description) BETWEEN 120 AND 160 THEN 15 ELSE 0 END +
                    CASE WHEN keywords IS NOT NULL THEN 10 ELSE 0 END +
                    CASE WHEN keywords IS NOT NULL AND LENGTH(keywords) - LENGTH(REPLACE(keywords, ",", "")) + 1 BETWEEN 3 AND 10 THEN 5 ELSE 0 END +
                    CASE WHEN canonical_url IS NOT NULL THEN 10 ELSE 0 END +
                    CASE WHEN structured_data IS NOT NULL THEN 5 ELSE 0 END
                ) >= 60 THEN "Good (60-79)"
                WHEN (
                    CASE WHEN title IS NOT NULL THEN 20 ELSE 0 END +
                    CASE WHEN title IS NOT NULL AND LENGTH(title) BETWEEN 30 AND 60 THEN 20 ELSE 0 END +
                    CASE WHEN description IS NOT NULL THEN 15 ELSE 0 END +
                    CASE WHEN description IS NOT NULL AND LENGTH(description) BETWEEN 120 AND 160 THEN 15 ELSE 0 END +
                    CASE WHEN keywords IS NOT NULL THEN 10 ELSE 0 END +
                    CASE WHEN keywords IS NOT NULL AND LENGTH(keywords) - LENGTH(REPLACE(keywords, ",", "")) + 1 BETWEEN 3 AND 10 THEN 5 ELSE 0 END +
                    CASE WHEN canonical_url IS NOT NULL THEN 10 ELSE 0 END +
                    CASE WHEN structured_data IS NOT NULL THEN 5 ELSE 0 END
                ) >= 40 THEN "Needs Improvement (40-59)"
                ELSE "Poor (0-39)"
            END as score_range,
            COUNT(*) as count
        ')->groupBy('score_range')->orderBy('count', 'desc')->get();
        $labels = $scores->pluck('score_range')->toArray();
        $data = $scores->pluck('count')->toArray();
        return ['datasets' => [['label' => __('admin.seo_data.fields.seo_score'), 'data' => $data, 'backgroundColor' => [
            '#10b981',
            // green for excellent
            '#f59e0b',
            // yellow for good
            '#ef4444',
            // red for needs improvement
            '#6b7280',
        ], 'borderColor' => ['#059669', '#d97706', '#dc2626', '#4b5563'], 'borderWidth' => 2]], 'labels' => $labels];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'doughnut';
    }
    /**
     * Handle getOptions functionality with proper error handling.
     * @return array
     */
    protected function getOptions(): array
    {
        return ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['position' => 'bottom'], 'tooltip' => ['callbacks' => ['label' => "function(context) {\n                            const total = context.dataset.data.reduce((a, b) => a + b, 0);\n                            const percentage = ((context.parsed / total) * 100).toFixed(1);\n                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';\n                        }"]]]];
    }
}