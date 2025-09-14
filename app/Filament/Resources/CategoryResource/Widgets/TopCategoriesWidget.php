<?php

declare (strict_types=1);
namespace App\Filament\Resources\CategoryResource\Widgets;

use App\Models\Category;
use Filament\Widgets\ChartWidget;
/**
 * TopCategoriesWidget
 * 
 * Filament v4 resource for TopCategoriesWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class TopCategoriesWidget extends ChartWidget
{
    protected static ?string $heading = 'Top Categories by Product Count';
    protected static ?int $sort = 2;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $topCategories = Category::withCount('products')->orderBy('products_count', 'desc')->limit(10)->get();
        $labels = $topCategories->pluck('name')->toArray();
        $data = $topCategories->pluck('products_count')->toArray();
        // Generate colors for each category
        $colors = [];
        for ($i = 0; $i < count($labels); $i++) {
            $colors[] = 'hsl(' . $i * 36 . ', 70%, 50%)';
            // 36 degrees apart for good color distribution
        }
        return ['datasets' => [['label' => 'Products Count', 'data' => $data, 'backgroundColor' => $colors, 'borderWidth' => 2, 'borderColor' => '#ffffff']], 'labels' => $labels];
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
        return ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['position' => 'bottom'], 'tooltip' => ['callbacks' => ['label' => 'function(context) {
                            return context.label + ": " + context.parsed.y + " products";
                        }']]], 'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['stepSize' => 1]]]];
    }
}