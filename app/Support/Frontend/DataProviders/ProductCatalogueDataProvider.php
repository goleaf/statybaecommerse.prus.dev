<?php

declare(strict_types=1);

namespace App\Support\Frontend\DataProviders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Scopes\PublishedScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ProductCatalogueDataProvider
{
    /**
     * Available sort options for catalogue listings.
     *
     * @var array<string, string>
     */
    private const SORT_OPTIONS = [
        'featured'    => 'Featured',
        'latest'      => 'Newest arrivals',
        'price_asc'   => 'Price: Low to High',
        'price_desc'  => 'Price: High to Low',
        'bestsellers' => 'Popular picks',
    ];

    /**
     * Supported filter shortcuts for the catalogue.
     *
     * @var array<string, string>
     */
    private const FILTER_OPTIONS = [
        'featured' => 'Featured only',
        'sale'     => 'On sale',
        'in_stock' => 'In stock',
    ];

    public function sortOptions(): array
    {
        return self::SORT_OPTIONS;
    }

    public function filterOptions(): array
    {
        return self::FILTER_OPTIONS;
    }

    public function resolveSortKey(?string $sort): string
    {
        return array_key_exists($sort ?? '', self::SORT_OPTIONS) ? (string) $sort : 'featured';
    }

    public function getListingData(array $filters = []): array
    {
        $query = $this->baseQuery();

        $this->applyCommonFilters($query, $filters);
        $this->applySort($query, $this->resolveSortKey($filters['sort'] ?? null));

        $products = $this->paginate($query, $filters);

        return [
            'products'         => $products,
            'availableSorts'   => $this->sortOptions(),
            'activeSort'       => $this->resolveSortKey($filters['sort'] ?? null),
            'availableFilters' => $this->filterOptions(),
            'activeFilter'     => $filters['filter'] ?? null,
            'categories'       => $this->categoryHighlights(),
            'brands'           => $this->brandHighlights(),
            'searchTerm'       => $filters['q'] ?? $filters['search'] ?? null,
        ];
    }

    public function getProductsForCategory(Category $category, array $filters = []): LengthAwarePaginator
    {
        $filtersWithCategory = $filters;
        $filtersWithCategory['category'] = $category->getKey();

        $query = $this->baseQuery();
        $this->applyCommonFilters($query, $filtersWithCategory);
        $this->applySort($query, $this->resolveSortKey($filtersWithCategory['sort'] ?? null));

        return $this->paginate($query, $filtersWithCategory);
    }

    public function getProductsForBrand(Brand $brand, array $filters = []): LengthAwarePaginator
    {
        $filtersWithBrand = $filters;
        $filtersWithBrand['brand'] = $brand->getKey();

        $query = $this->baseQuery();
        $this->applyCommonFilters($query, $filtersWithBrand);
        $this->applySort($query, $this->resolveSortKey($filtersWithBrand['sort'] ?? null));

        return $this->paginate($query, $filtersWithBrand);
    }

    public function getProductDetailData(Product $product): array
    {
        $product->loadMissing(['brand', 'categories']);

        $relatedProducts = $this->baseQuery()
            ->whereKeyNot($product->getKey())
            ->when($product->categories->isNotEmpty(), function (Builder $query) use ($product): void {
                $query->whereHas('categories', function (Builder $categoryQuery) use ($product): void {
                    $categoryQuery->whereIn('categories.id', $product->categories->pluck('id'));
                });
            })
            ->limit(8)
            ->get();

        return [
            'product'         => $product,
            'relatedProducts' => $relatedProducts,
            'primaryCategory' => $product->categories->first(),
        ];
    }

    public function featured(int $limit = 8): Collection
    {
        return $this->baseQuery()
            ->where('is_featured', true)
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function latest(int $limit = 8): Collection
    {
        return $this->baseQuery()
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function trending(int $limit = 8): Collection
    {
        return $this->baseQuery()
            ->orderByDesc('requests_count')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function onSale(int $limit = 8): Collection
    {
        return $this->baseQuery()
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    private function baseQuery(): Builder
    {
        return Product::query()
            ->withoutGlobalScope(PublishedScope::class)
            ->with(['brand'])
            ->where('is_visible', true)
            ->whereIn('status', ['active', 'published'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    private function applyCommonFilters(Builder $query, array $filters): void
    {
        if ($search = $filters['q'] ?? $filters['search'] ?? null) {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }

        if (($filter = $filters['filter'] ?? null) && array_key_exists($filter, self::FILTER_OPTIONS)) {
            $this->applyFilterShortcut($query, $filter);
        }

        if (null !== ($brandId = $this->resolveBrandIdentifier($filters['brand'] ?? $filters['brand_id'] ?? null))) {
            $query->where('brand_id', $brandId);
        }

        if (null !== ($categoryId = $this->resolveCategoryIdentifier($filters['category'] ?? $filters['category_id'] ?? null))) {
            $query->whereHas('categories', function (Builder $builder) use ($categoryId): void {
                $builder->where('categories.id', $categoryId);
            });
        }

        if (($minPrice = $filters['price_min'] ?? null) !== null) {
            $query->where('price', '>=', $minPrice);
        }

        if (($maxPrice = $filters['price_max'] ?? null) !== null) {
            $query->where('price', '<=', $maxPrice);
        }
    }

    private function applyFilterShortcut(Builder $query, string $filter): void
    {
        match ($filter) {
            'featured' => $query->where('is_featured', true),
            'sale'     => $query->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price'),
            'in_stock' => $query->where(static function (Builder $builder): void {
                $builder->where('manage_stock', false)
                    ->orWhere('stock_quantity', '>', 0);
            }),
            default => null,
        };
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'latest'      => $query->orderByDesc('published_at'),
            'price_asc'   => $query->orderBy('price')->orderBy('name'),
            'price_desc'  => $query->orderByDesc('price')->orderBy('name'),
            'bestsellers' => $query->orderByDesc('requests_count')->orderByDesc('published_at'),
            default       => $query->orderByDesc('is_featured')->orderByDesc('published_at'),
        };
    }

    private function paginate(Builder $query, array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 12);

        return $query->paginate($perPage > 0 ? $perPage : 12)->withQueryString();
    }

    private function resolveBrandIdentifier(mixed $brand): ?int
    {
        if ($brand instanceof Brand) {
            return $brand->getKey();
        }

        if ($brand === null || $brand === '') {
            return null;
        }

        if (is_int($brand)) {
            return $brand;
        }

        if (is_string($brand) && ctype_digit($brand)) {
            return (int) $brand;
        }

        if (is_scalar($brand)) {
            $slug = (string) $brand;

            $brandId = Brand::query()
                ->where(static function (Builder $builder) use ($slug): void {
                    $builder->where('slug', $slug)
                        ->orWhereHas('translations', static function (Builder $translationQuery) use ($slug): void {
                            $translationQuery->where('slug', $slug);
                        });
                })
                ->value('id');

            return $brandId !== null ? (int) $brandId : null;
        }

        return null;
    }

    private function resolveCategoryIdentifier(mixed $category): ?int
    {
        if ($category instanceof Category) {
            return $category->getKey();
        }

        if ($category === null || $category === '') {
            return null;
        }

        if (is_int($category)) {
            return $category;
        }

        if (is_string($category) && ctype_digit($category)) {
            return (int) $category;
        }

        if (is_scalar($category)) {
            $slug = (string) $category;

            $categoryId = Category::query()
                ->where(static function (Builder $builder) use ($slug): void {
                    $builder->where('slug', $slug)
                        ->orWhereHas('translations', static function (Builder $translationQuery) use ($slug): void {
                            $translationQuery->where('slug', $slug);
                        });
                })
                ->value('id');

            return $categoryId !== null ? (int) $categoryId : null;
        }

        return null;
    }

    public function categoryHighlights(int $limit = 6): Collection
    {
        return Category::query()
            ->withCount(['products as published_products_count' => static function (Builder $builder): void {
                $builder->published();
            }])
            ->whereHas('products', static function (Builder $builder): void {
                $builder->published();
            })
            ->orderByDesc('published_products_count')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function brandHighlights(int $limit = 8): Collection
    {
        return Brand::query()
            ->where('is_visible', true)
            ->withCount(['products as published_products_count' => static function (Builder $builder): void {
                $builder->published();
            }])
            ->whereHas('products', static function (Builder $builder): void {
                $builder->published();
            })
            ->orderByDesc('published_products_count')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}
