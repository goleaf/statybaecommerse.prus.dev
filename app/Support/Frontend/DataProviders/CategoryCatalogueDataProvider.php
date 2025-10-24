<?php

declare(strict_types=1);

namespace App\Support\Frontend\DataProviders;

use App\Models\Brand;
use App\Models\Category;
use App\Support\Frontend\DataProviders\Concerns\BuildsProductCatalogueQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class CategoryCatalogueDataProvider
{
    use BuildsProductCatalogueQuery;

    public function __construct(private readonly ProductCatalogueDataProvider $products) {}

    public function index(): array
    {
        $categories = Category::query()
            ->with(['children' => static function (Builder $query): void {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return [
            'categories' => $categories,
        ];
    }

    public function show(Category $category, array $filters = []): array
    {
        $category->loadMissing(['parent', 'children']);

        $products = $this->products->getProductsForCategory($category, $filters);

        return [
            'category' => $category,
            'products' => $products,
            'availableSorts' => $this->products->sortOptions(),
            'availableFilters' => $this->products->filterOptions(),
            'activeSort' => $this->products->resolveSortKey($filters['sort'] ?? null),
            'activeFilter' => $filters['filter'] ?? null,
            'breadcrumbs' => $this->buildBreadcrumbs($category),
            'relatedCategories' => $this->resolveRelatedCategories($category),
            'highlightedBrands' => $this->resolveCategoryBrands($category),
        ];
    }

    private function buildBreadcrumbs(Category $category): Collection
    {
        $segments = collect();
        $current = $category;

        while ($current) {
            $segments->prepend($current);
            $current = $current->parent;
        }

        return $segments;
    }

    private function resolveRelatedCategories(Category $category): Collection
    {
        if ($category->children->isNotEmpty()) {
            return $category->children->take(6);
        }

        return Category::query()
            ->where('parent_id', $category->parent_id)
            ->whereKeyNot($category->getKey())
            ->limit(6)
            ->get();
    }

    private function resolveCategoryBrands(Category $category): Collection
    {
        return Brand::query()
            ->where('is_visible', true)
            ->whereHas('products', function (Builder $query) use ($category): void {
                $query->published()->whereHas('categories', function (Builder $builder) use ($category): void {
                    $builder->where('category_id', $category->getKey());
                });
            })
            ->limit(8)
            ->get();
    }
}
