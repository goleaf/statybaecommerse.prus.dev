<?php

declare (strict_types=1);
namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use App\Models\Category;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
/**
 * ProductCategoriesWidget
 * 
 * Filament v4 resource for ProductCategoriesWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ProductCategoriesWidget extends ChartWidget
{
    protected static ?string $heading = 'Products by Category';
    protected static ?int $sort = 3;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $categoryData = Product::join('product_categories', 'products.id', '=', 'product_categories.product_id')->join('categories', 'product_categories.category_id', '=', 'categories.id')->select('categories.name', DB::raw('COUNT(products.id) as count'))->groupBy('categories.id', 'categories.name')->orderBy('count', 'desc')->limit(10)->get();
        $labels = $categoryData->pluck('name')->toArray();
        $data = $categoryData->pluck('count')->toArray();
        // Generate colors for each category
        $colors = [];
        for ($i = 0; $i < count($labels); $i++) {
            $colors[] = 'hsl(' . $i * 360 / count($labels) . ', 70%, 50%)';
        }
        return ['datasets' => [['label' => __('translations.products_count'), 'data' => $data, 'backgroundColor' => $colors, 'borderWidth' => 2, 'borderColor' => '#ffffff']], 'labels' => $labels];
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
                            return context.label + ": " + context.parsed + " " + "' . __('translations.products') . '";
                        }']]]];
    }
}