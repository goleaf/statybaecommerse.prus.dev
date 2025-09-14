<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Zone;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
/**
 * ZoneTypeChartWidget
 * 
 * Filament v4 widget for ZoneTypeChartWidget dashboard display with real-time data and interactive features.
 * 
 * @property string|null $heading
 * @property int|string|array $columnSpan
 * @property int|null $sort
 */
final class ZoneTypeChartWidget extends ChartWidget
{
    protected ?string $heading = 'zones.zone_distribution';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 2;
    /**
     * Handle getDescription functionality with proper error handling.
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return 'zones.zone_type_distribution_desc';
    }
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $typeStats = Zone::select('type', DB::raw('count(*) as count'))->groupBy('type')->orderBy('count', 'desc')->get();
        $labels = $typeStats->pluck('type')->map(fn($type) => match ($type) {
            'shipping' => __('zones.type_shipping'),
            'tax' => __('zones.type_tax'),
            'payment' => __('zones.type_payment'),
            'delivery' => __('zones.type_delivery'),
            'general' => __('zones.type_general'),
            default => ucfirst($type),
        })->toArray();
        $data = $typeStats->pluck('count')->toArray();
        return ['datasets' => [['label' => __('zones.zone_count'), 'data' => $data, 'backgroundColor' => [
            'rgba(59, 130, 246, 0.8)',
            // Blue for shipping
            'rgba(245, 158, 11, 0.8)',
            // Yellow for tax
            'rgba(16, 185, 129, 0.8)',
            // Green for payment
            'rgba(139, 92, 246, 0.8)',
            // Purple for delivery
            'rgba(107, 114, 128, 0.8)',
        ], 'borderColor' => ['rgba(59, 130, 246, 1)', 'rgba(245, 158, 11, 1)', 'rgba(16, 185, 129, 1)', 'rgba(139, 92, 246, 1)', 'rgba(107, 114, 128, 1)'], 'borderWidth' => 2]], 'labels' => $labels];
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
        return ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['position' => 'bottom'], 'tooltip' => ['callbacks' => ['label' => 'function(context) {
                            const label = context.label || "";
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ": " + value + " (" + percentage + "%)";
                        }']]]];
    }
}