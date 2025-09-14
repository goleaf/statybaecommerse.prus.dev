<?php

declare (strict_types=1);
namespace App\Filament\Resources\ActivityLogResource\Widgets;

use Filament\Widgets\ChartWidget;
use Spatie\Activitylog\Models\Activity;
/**
 * ActivityLogChartWidget
 * 
 * Filament v4 resource for ActivityLogChartWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ActivityLogChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Activity Logs Over Time';
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $data = Activity::selectRaw('DATE(created_at) as date, COUNT(*) as count')->where('created_at', '>=', now()->subDays(30))->groupBy('date')->orderBy('date')->get();
        return ['datasets' => [['label' => __('admin.activity_logs.chart.activities'), 'data' => $data->pluck('count')->toArray(), 'backgroundColor' => 'rgba(59, 130, 246, 0.1)', 'borderColor' => 'rgba(59, 130, 246, 1)', 'borderWidth' => 2]], 'labels' => $data->pluck('date')->map(fn($date) => \Carbon\Carbon::parse($date)->format('M d'))->toArray()];
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
        return ['responsive' => true, 'maintainAspectRatio' => false, 'scales' => ['y' => ['beginAtZero' => true]]];
    }
}