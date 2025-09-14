<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\ProductHistory;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
/**
 * ProductHistoryOverviewWidget
 * 
 * Filament v4 widget for ProductHistoryOverviewWidget dashboard display with real-time data and interactive features.
 * 
 * @property int|null $sort
 * @property int|string|array $columnSpan
 * @property string|null $filter
 */
final class ProductHistoryOverviewWidget extends ChartWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    public ?string $filter = '30';
    /**
     * Handle getHeading functionality with proper error handling.
     * @return string
     */
    public function getHeading(): string
    {
        return 'Product Changes Over Time';
    }
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $data = ProductHistory::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))->where('created_at', '>=', now()->subDays((int) $this->filter))->groupBy('date')->orderBy('date')->get();
        return ['datasets' => [['label' => 'Product Changes', 'data' => $data->pluck('count'), 'borderColor' => 'rgb(59, 130, 246)', 'backgroundColor' => 'rgba(59, 130, 246, 0.1)', 'fill' => true]], 'labels' => $data->pluck('date')];
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
     * Handle getFilters functionality with proper error handling.
     * @return array|null
     */
    protected function getFilters(): ?array
    {
        return ['7' => 'Last 7 days', '30' => 'Last 30 days', '90' => 'Last 90 days', '365' => 'Last year'];
    }
}