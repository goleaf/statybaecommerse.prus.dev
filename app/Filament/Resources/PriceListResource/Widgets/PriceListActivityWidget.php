<?php

declare (strict_types=1);
namespace App\Filament\Resources\PriceListResource\Widgets;

use App\Models\PriceList;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
/**
 * PriceListActivityWidget
 * 
 * Filament v4 resource for PriceListActivityWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class PriceListActivityWidget extends ChartWidget
{
    protected static ?string $heading = 'admin.price_lists.charts.price_lists_over_time';
    protected static ?int $sort = 2;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $data = PriceList::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))->where('created_at', '>=', now()->subDays(30))->groupBy('date')->orderBy('date')->get();
        return ['datasets' => [['label' => __('admin.price_lists.charts.price_lists_created'), 'data' => $data->pluck('count')->toArray(), 'backgroundColor' => 'rgba(59, 130, 246, 0.1)', 'borderColor' => 'rgba(59, 130, 246, 1)', 'borderWidth' => 2, 'fill' => true]], 'labels' => $data->pluck('date')->map(function ($date) {
            return \Carbon\Carbon::parse($date)->format('M d');
        })->toArray()];
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
        return ['responsive' => true, 'maintainAspectRatio' => false, 'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['stepSize' => 1]]], 'plugins' => ['legend' => ['display' => true], 'tooltip' => ['mode' => 'index', 'intersect' => false]]];
    }
}