<?php

declare (strict_types=1);
namespace App\Filament\Resources\BrandResource\Widgets;

use App\Models\Brand;
use Filament\Widgets\ChartWidget;
/**
 * BrandPerformanceWidget
 * 
 * Filament v4 resource for BrandPerformanceWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|string|array $columnSpan
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class BrandPerformanceWidget extends ChartWidget
{
    protected static ?string $heading = 'admin.brands.widgets.performance_heading';
    protected int|string|array $columnSpan = 'full';
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $brands = Brand::withCount(['products', 'translations'])->orderBy('products_count', 'desc')->limit(10)->get();
        return ['datasets' => [['label' => __('admin.brands.widgets.products_count'), 'data' => $brands->pluck('products_count')->toArray(), 'backgroundColor' => 'rgba(59, 130, 246, 0.5)', 'borderColor' => 'rgb(59, 130, 246)', 'borderWidth' => 2], ['label' => __('admin.brands.widgets.translations_count'), 'data' => $brands->pluck('translations_count')->toArray(), 'backgroundColor' => 'rgba(16, 185, 129, 0.5)', 'borderColor' => 'rgb(16, 185, 129)', 'borderWidth' => 2]], 'labels' => $brands->pluck('name')->toArray()];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'bar';
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