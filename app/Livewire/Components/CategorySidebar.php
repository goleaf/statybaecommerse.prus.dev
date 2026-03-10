<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Category;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\TagAwareCache;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * CategorySidebar
 *
 * Livewire component for CategorySidebar with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property int|null $selectedCategoryId
 * @property int      $maxDepth
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

        return TagAwareCache::remember("category_tree:v2:{$locale}", now()->addMinutes(30), function () use ($locale) {
            $allVisibleCategories = Category::query()
                ->withProductCounts()
                ->with([
                    'translations' => fn ($q) => $q->where('locale', $locale),
                ])
                ->visible()
                ->ordered()
                ->get();

            $categoriesById = [];
            $childrenByParentId = [];
            $directProductCounts = [];

            foreach ($allVisibleCategories as $category) {
                $categoryId = (int) $category->id;
                $parentId = $category->parent_id !== null ? (int) $category->parent_id : null;

                $categoriesById[$categoryId] = $category;
                $childrenByParentId[$parentId][] = $categoryId;
                $directProductCounts[$categoryId] = (int) ($category->products_count ?? 0);
            }

            $aggregateCounts = [];

            return $this->buildTreeFromCategoryIds(
                $childrenByParentId[null] ?? [],
                0,
                $categoriesById,
                $childrenByParentId,
                $directProductCounts,
                $aggregateCounts
            );
        }, [CacheKeys::homeTag()]);
    }

    /**
     * Build tree nodes for the sidebar up to maxDepth while keeping aggregate
     * counts calculated across the full descendant hierarchy.
     *
     * @param array<int, int>                  $categoryIds
     * @param array<int, \App\Models\Category> $categoriesById
     * @param array<int|null, array<int, int>> $childrenByParentId
     * @param array<int, int>                  $directProductCounts
     * @param array<int, int>                  $aggregateCounts
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildTreeFromCategoryIds(
        array $categoryIds,
        int $depth,
        array $categoriesById,
        array $childrenByParentId,
        array $directProductCounts,
        array &$aggregateCounts
    ): array {
        if ($depth >= $this->maxDepth) {
            return [];
        }

        $tree = [];

        foreach ($categoryIds as $categoryId) {
            $category = $categoriesById[$categoryId] ?? null;

            if (! $category instanceof Category) {
                continue;
            }

            $childIds = $childrenByParentId[$categoryId] ?? [];
            $children = $this->buildTreeFromCategoryIds(
                $childIds,
                $depth + 1,
                $categoriesById,
                $childrenByParentId,
                $directProductCounts,
                $aggregateCounts
            );

            $tree[] = [
                'id'                       => $categoryId,
                'slug'                     => method_exists($category, 'trans') ? $category->trans('slug') ?? $category->slug : $category->slug,
                'name'                     => method_exists($category, 'trans') ? $category->trans('name') ?? $category->name : $category->name,
                'description'              => $category->description,
                'products_count'           => $directProductCounts[$categoryId] ?? 0,
                'aggregate_products_count' => $this->resolveAggregateProductsCount($categoryId, $childrenByParentId, $directProductCounts, $aggregateCounts),
                'has_children'             => $childIds !== [],
                'children'                 => $children,
                'depth'                    => $depth,
            ];
        }

        return $tree;
    }

    /**
     * Resolve product count as (self + all descendants) with memoization.
     *
     * @param array<int|null, array<int, int>> $childrenByParentId
     * @param array<int, int>                  $directProductCounts
     * @param array<int, int>                  $aggregateCounts
     */
    private function resolveAggregateProductsCount(
        int $categoryId,
        array $childrenByParentId,
        array $directProductCounts,
        array &$aggregateCounts
    ): int {
        if (array_key_exists($categoryId, $aggregateCounts)) {
            return $aggregateCounts[$categoryId];
        }

        $total = (int) ($directProductCounts[$categoryId] ?? 0);

        foreach ($childrenByParentId[$categoryId] ?? [] as $childId) {
            $total += $this->resolveAggregateProductsCount($childId, $childrenByParentId, $directProductCounts, $aggregateCounts);
        }

        $aggregateCounts[$categoryId] = $total;

        return $total;
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
