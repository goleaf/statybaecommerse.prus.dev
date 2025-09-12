<?php declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class MobileCategoryMenu extends Component
{
    #[Computed]
    public function categoryTree()
    {
        $locale = app()->getLocale();
        return Cache::remember("mobile_category_tree:{$locale}", now()->addMinutes(30), function () {
            $roots = Category::query()
                ->withProductCounts()
                ->with([
                    'translations' => fn($q) => $q->where('locale', app()->getLocale()),
                    'children' => function ($q) {
                        $q
                            ->withProductCounts()
                            ->with(['translations' => fn($q) => $q->where('locale', app()->getLocale())])
                            ->visible()
                            ->ordered()
                            ->limit(10);  // Limit for mobile
                    }
                ])
                ->visible()
                ->roots()
                ->ordered()
                ->limit(8)  // Limit main categories for mobile
                ->get();

            return $roots->map(function ($category) {
                return [
                    'id' => $category->id,
                    'slug' => method_exists($category, 'trans')
                        ? ($category->trans('slug') ?? $category->slug)
                        : $category->slug,
                    'name' => method_exists($category, 'trans')
                        ? ($category->trans('name') ?? $category->name)
                        : $category->name,
                    'products_count' => $category->products_count ?? 0,
                    'has_children' => $category->children->isNotEmpty(),
                    'children' => $category->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'slug' => method_exists($child, 'trans')
                                ? ($child->trans('slug') ?? $child->slug)
                                : $child->slug,
                            'name' => method_exists($child, 'trans')
                                ? ($child->trans('name') ?? $child->name)
                                : $child->name,
                            'products_count' => $child->products_count ?? 0,
                        ];
                    })->toArray(),
                ];
            })->toArray();
        });
    }

    public function render(): View
    {
        return view('livewire.components.mobile-category-menu');
    }
}
