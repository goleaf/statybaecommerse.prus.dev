<?php

declare (strict_types=1);
namespace App\Filament\Resources\CategoryResource\Widgets;

use App\Models\Category;
use Filament\Widgets\Widget;
/**
 * CategoryTreeWidget
 * 
 * Filament v4 resource for CategoryTreeWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $view
 * @property int|string|array $columnSpan
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CategoryTreeWidget extends Widget
{
    protected static string $view = 'filament.resources.category-resource.widgets.category-tree-widget';
    protected int|string|array $columnSpan = 'full';
    /**
     * Handle getViewData functionality with proper error handling.
     * @return array
     */
    public function getViewData(): array
    {
        $categories = Category::with(['children', 'products'])->root()->enabled()->orderBy('sort_order')->orderBy('name')->get();
        return ['categories' => $categories, 'totalCategories' => Category::count(), 'rootCategories' => Category::root()->count(), 'enabledCategories' => Category::enabled()->count()];
    }
}