<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
/**
 * ProductChartWidget
 * 
 * Filament v4 widget for ProductChartWidget dashboard display with real-time data and interactive features.
 * 
 * @property string|null $heading
 * @property int|null $sort
 */
class ProductChartWidget extends ChartWidget
{
    protected ?string $heading = 'Product Status Distribution';
    protected static ?int $sort = 2;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $statusData = Product::select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status')->toArray();
        $visibilityData = Product::select('is_visible', DB::raw('count(*) as count'))->groupBy('is_visible')->pluck('count', 'is_visible')->toArray();
        return ['datasets' => [['label' => __('translations.products_by_status'), 'data' => [$statusData['draft'] ?? 0, $statusData['published'] ?? 0, $statusData['archived'] ?? 0], 'backgroundColor' => [
            'rgb(156, 163, 175)',
            // gray for draft
            'rgb(34, 197, 94)',
            // green for published
            'rgb(239, 68, 68)',
        ]], ['label' => __('translations.products_by_visibility'), 'data' => [
            $visibilityData[1] ?? 0,
            // visible
            $visibilityData[0] ?? 0,
        ], 'backgroundColor' => [
            'rgb(59, 130, 246)',
            // blue for visible
            'rgb(107, 114, 128)',
        ]]], 'labels' => [__('translations.draft'), __('translations.published'), __('translations.archived'), __('translations.visible'), __('translations.hidden')]];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'doughnut';
    }
}