<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Address;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
/**
 * AddressGrowthChartWidget
 * 
 * Filament v4 widget for AddressGrowthChartWidget dashboard display with real-time data and interactive features.
 * 
 * @property int|null $sort
 */
final class AddressGrowthChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;
    /**
     * Handle getHeading functionality with proper error handling.
     * @return string
     */
    public function getHeading(): string
    {
        return 'Address Growth Over Time';
    }
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $data = Address::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))->where('created_at', '>=', now()->subDays(30))->groupBy('date')->orderBy('date')->get();
        $labels = $data->pluck('date')->map(function ($date) {
            return \Carbon\Carbon::parse($date)->format('M d');
        })->toArray();
        $counts = $data->pluck('count')->toArray();
        return ['datasets' => [['label' => __('translations.new_addresses'), 'data' => $counts, 'backgroundColor' => 'rgba(59, 130, 246, 0.1)', 'borderColor' => '#3B82F6', 'borderWidth' => 2, 'fill' => true, 'tension' => 0.4]], 'labels' => $labels];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'line';
    }
    /**
     * Handle getOptions functionality with proper error handling.
     * @return array
     */
    protected function getOptions(): array
    {
        return ['responsive' => true, 'maintainAspectRatio' => false, 'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['stepSize' => 1]]], 'plugins' => ['legend' => ['display' => true, 'position' => 'top'], 'tooltip' => ['mode' => 'index', 'intersect' => false]]];
    }
}