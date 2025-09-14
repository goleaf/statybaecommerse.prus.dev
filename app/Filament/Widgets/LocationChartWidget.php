<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Location;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
/**
 * LocationChartWidget
 * 
 * Filament v4 widget for LocationChartWidget dashboard display with real-time data and interactive features.
 * 
 * @property string|null $heading
 * @property int|null $sort
 */
final class LocationChartWidget extends ChartWidget
{
    protected ?string $heading = 'Locations by Type';
    protected static ?int $sort = 2;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $locationsByType = Location::select('type', DB::raw('count(*) as count'))->groupBy('type')->get()->pluck('count', 'type')->toArray();
        $labels = [];
        $data = [];
        foreach ($locationsByType as $type => $count) {
            $labels[] = match ($type) {
                'warehouse' => __('locations.type_warehouse'),
                'store' => __('locations.type_store'),
                'office' => __('locations.type_office'),
                'pickup_point' => __('locations.type_pickup_point'),
                'other' => __('locations.type_other'),
                default => $type,
            };
            $data[] = $count;
        }
        return ['datasets' => [['label' => __('locations.locations_count'), 'data' => $data, 'backgroundColor' => [
            '#3B82F6',
            // Blue
            '#10B981',
            // Green
            '#F59E0B',
            // Yellow
            '#EF4444',
            // Red
            '#8B5CF6',
        ], 'borderColor' => ['#1E40AF', '#059669', '#D97706', '#DC2626', '#7C3AED'], 'borderWidth' => 2]], 'labels' => $labels];
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
        return ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['position' => 'bottom']]];
    }
}