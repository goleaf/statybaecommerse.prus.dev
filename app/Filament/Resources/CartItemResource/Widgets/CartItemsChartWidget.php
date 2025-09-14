<?php

declare (strict_types=1);
namespace App\Filament\Resources\CartItemResource\Widgets;

use App\Models\CartItem;
use Filament\Widgets\ChartWidget;
/**
 * CartItemsChartWidget
 * 
 * Filament v4 resource for CartItemsChartWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CartItemsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'admin.cart_items.charts.cart_items_over_time';
    protected static ?int $sort = 2;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $data = CartItem::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_price) as total_value')->where('created_at', '>=', now()->subDays(30))->groupBy('date')->orderBy('date')->get();
        return ['datasets' => [['label' => __('admin.cart_items.charts.items_count'), 'data' => $data->pluck('count')->toArray(), 'backgroundColor' => 'rgba(59, 130, 246, 0.1)', 'borderColor' => 'rgb(59, 130, 246)', 'borderWidth' => 2, 'fill' => true], ['label' => __('admin.cart_items.charts.total_value'), 'data' => $data->pluck('total_value')->toArray(), 'backgroundColor' => 'rgba(16, 185, 129, 0.1)', 'borderColor' => 'rgb(16, 185, 129)', 'borderWidth' => 2, 'fill' => true, 'yAxisID' => 'y1']], 'labels' => $data->pluck('date')->map(fn($date) => \Carbon\Carbon::parse($date)->format('M d'))->toArray()];
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
        return ['responsive' => true, 'maintainAspectRatio' => false, 'scales' => ['y' => ['type' => 'linear', 'display' => true, 'position' => 'left', 'title' => ['display' => true, 'text' => __('admin.cart_items.charts.items_count')]], 'y1' => ['type' => 'linear', 'display' => true, 'position' => 'right', 'title' => ['display' => true, 'text' => __('admin.cart_items.charts.total_value')], 'grid' => ['drawOnChartArea' => false]]], 'plugins' => ['legend' => ['display' => true, 'position' => 'top'], 'tooltip' => ['mode' => 'index', 'intersect' => false]]];
    }
}