<?php

declare(strict_types=1);

namespace App\Support\Frontend\DataProviders;

use App\Models\Category;
use App\Support\Frontend\DataProviders\Concerns\BuildsProductCatalogueQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class CategoryCatalogueDataProvider
{
    use BuildsProductCatalogueQuery;

    /**
     * Category detail pages intentionally expose a narrower quick-filter set than
     * the shared catalogue pages.
     *
     * @var array<int, string>
     */
    private const EXCLUDED_FILTERS = ['featured'];

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
        $category->loadMissing('parent');
        $filters = $this->sanitizeFilters($filters);

        $products = $this->products->getProductsForCategory($category, $filters);

        return [
            'category'          => $category,
            'products'          => $products,
            'availableSorts'    => $this->products->sortOptions(),
            'availableFilters'  => $this->availableFilters(),
            'activeSort'        => $this->products->resolveSortKey($filters['sort'] ?? null),
            'activeFilter'      => $filters['filter'] ?? null,
            'breadcrumbs'       => $this->buildBreadcrumbs($category),
            'relatedCategories' => $this->resolveRelatedCategories($category),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function availableFilters(): array
    {
        return array_diff_key(
            $this->products->filterOptions(),
            array_flip(self::EXCLUDED_FILTERS)
        );
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    private function sanitizeFilters(array $filters): array
    {
        $requestedFilter = $filters['filter'] ?? null;

        if (is_string($requestedFilter) && in_array($requestedFilter, self::EXCLUDED_FILTERS, true)) {
            unset($filters['filter']);
        }

        return $filters;
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
        $publishedProductCount = [
            'products as published_products_count' => static fn (Builder $query): Builder => $query->published(),
        ];

        $children = $category->children()
            ->withCount($publishedProductCount)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(6)
            ->get();

        if ($children->isNotEmpty()) {
            return $children;
        }

        return Category::query()
            ->where('parent_id', $category->parent_id)
            ->whereKeyNot($category->getKey())
            ->withCount($publishedProductCount)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(6)
            ->get();
    }

}
