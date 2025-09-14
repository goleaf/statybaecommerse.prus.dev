<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Legal;
use Filament\Widgets\ChartWidget;
/**
 * LegalStatusChartWidget
 * 
 * Filament v4 widget for LegalStatusChartWidget dashboard display with real-time data and interactive features.
 * 
 * @property string|null $heading
 * @property int|null $sort
 */
class LegalStatusChartWidget extends ChartWidget
{
    protected ?string $heading = 'Legal Documents Status Overview';
    protected static ?int $sort = 3;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $published = Legal::where('is_enabled', true)->whereNotNull('published_at')->count();
        $draft = Legal::whereNull('published_at')->count();
        $disabled = Legal::where('is_enabled', false)->count();
        return ['datasets' => [['label' => __('admin.legal.documents_count'), 'data' => [$published, $draft, $disabled], 'backgroundColor' => [
            '#22c55e',
            // green for published
            '#eab308',
            // yellow for draft
            '#ef4444',
        ], 'borderColor' => '#ffffff', 'borderWidth' => 2]], 'labels' => [__('admin.legal.status_published'), __('admin.legal.status_draft'), __('admin.legal.status_disabled')]];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'pie';
    }
    /**
     * Handle getOptions functionality with proper error handling.
     * @return array
     */
    protected function getOptions(): array
    {
        return ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['position' => 'bottom'], 'tooltip' => ['callbacks' => ['label' => 'function(context) {
                            const label = context.label || "";
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return label + ": " + value + " (" + percentage + "%)";
                        }']]]];
    }
}