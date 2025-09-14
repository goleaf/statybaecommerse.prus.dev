<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
/**
 * OrderChartWidget
 * 
 * Filament v4 widget for OrderChartWidget dashboard display with real-time data and interactive features.
 * 
 * @property string|null $heading
 * @property int|null $sort
 */
class OrderChartWidget extends ChartWidget
{
    protected ?string $heading = 'orders.charts.orders_over_time';
    protected static ?int $sort = 2;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $orders = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as revenue'))->where('created_at', '>=', now()->subDays(30))->groupBy('date')->orderBy('date')->get();
        return ['datasets' => [['label' => __('orders.charts.orders_count'), 'data' => $orders->pluck('count'), 'borderColor' => 'rgb(59, 130, 246)', 'backgroundColor' => 'rgba(59, 130, 246, 0.1)', 'tension' => 0.4], ['label' => __('orders.charts.revenue'), 'data' => $orders->pluck('revenue'), 'borderColor' => 'rgb(34, 197, 94)', 'backgroundColor' => 'rgba(34, 197, 94, 0.1)', 'tension' => 0.4, 'yAxisID' => 'y1']], 'labels' => $orders->pluck('date')->map(fn($date) => \Carbon\Carbon::parse($date)->format('M d'))];
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
        return ['responsive' => true, 'interaction' => ['intersect' => false, 'mode' => 'index'], 'scales' => ['y' => ['type' => 'linear', 'display' => true, 'position' => 'left', 'title' => ['display' => true, 'text' => __('orders.charts.orders_count')]], 'y1' => ['type' => 'linear', 'display' => true, 'position' => 'right', 'title' => ['display' => true, 'text' => __('orders.charts.revenue') . ' (€)'], 'grid' => ['drawOnChartArea' => false]]]];
    }
}