<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\Widgets;

use App\Models\Category;
use Filament\Widgets\Widget;

final /**
 * CategoryTreeWidget
 * 
 * Filament resource for admin panel management.
 */
class CategoryTreeWidget extends Widget
{
    protected static string $view = 'filament.resources.category-resource.widgets.category-tree-widget';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $categories = Category::with(['children', 'products'])
            ->root()
            ->enabled()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return [
            'categories' => $categories,
            'totalCategories' => Category::count(),
            'rootCategories' => Category::root()->count(),
            'enabledCategories' => Category::enabled()->count(),
        ];
    }
}
