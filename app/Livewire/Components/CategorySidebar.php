<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * CategorySidebar
 *
 * Livewire component for CategorySidebar with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property int|null $selectedCategoryId
 * @property int $maxDepth
 */
final class CategorySidebar extends Component
{
    public ?int $selectedCategoryId = null;

    public int $maxDepth = 3;

    /**
     * Handle categoryTree functionality with proper error handling.
     */
    #[Computed]
    public function categoryTree()
    {
        $locale = app()->getLocale();

        return Cache::remember("category_tree:{$locale}", now()->addMinutes(30), function () {
            $roots = Category::query()->withProductCounts()->with(['translations' => fn ($q) => $q->where('locale', app()->getLocale()), 'children' => function ($q) {
                $q->withProductCounts()->with(['translations' => fn ($q) => $q->where('locale', app()->getLocale())])->visible()->ordered();
            }])->visible()->roots()->ordered()->get();

            return $this->buildTree($roots, 0);
        });
    }

    /**
     * Handle buildTree functionality with proper error handling.
     *
     * @param  mixed  $categories
     */
    private function buildTree($categories, int $depth): array
    {
        if ($depth >= $this->maxDepth) {
            return [];
        }

        return $categories->map(function ($category) use ($depth) {
            $children = $category->children->isNotEmpty() ? $this->buildTree($category->children, $depth + 1) : [];

            return ['id' => $category->id, 'slug' => method_exists($category, 'trans') ? $category->trans('slug') ?? $category->slug : $category->slug, 'name' => method_exists($category, 'trans') ? $category->trans('name') ?? $category->name : $category->name, 'description' => $category->description, 'products_count' => $category->products_count ?? 0, 'has_children' => $category->children->isNotEmpty(), 'children' => $children, 'depth' => $depth];
        })->toArray();
    }

    /**
     * Handle selectCategory functionality with proper error handling.
     */
    public function selectCategory(int $categoryId): void
    {
        $this->selectedCategoryId = $categoryId;
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.category-sidebar');
    }
}
