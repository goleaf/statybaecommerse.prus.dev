<?php

declare(strict_types=1);

namespace App\Support\Frontend\DataProviders;

use App\Models\Brand;
use App\Models\Category;
use App\Support\Frontend\DataProviders\Concerns\BuildsProductCatalogueQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class BrandCatalogueDataProvider
{
    use BuildsProductCatalogueQuery;

    public function __construct(private readonly ProductCatalogueDataProvider $products) {}

    public function index(array $filters = []): array
    {
        $search = trim((string) ($filters['q'] ?? $filters['search'] ?? ''));

        $brandsQuery = Brand::query()
            ->where('is_visible', true)
            ->withCount([
                'products as published_products_count' => static function (Builder $query): void {
                    $query->published();
                },
            ])
            ->when($search !== '', static function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderByDesc('published_products_count')
            ->orderBy('name');

        $brands = $brandsQuery
            ->paginate(24)
            ->withQueryString();

        return [
            'brands' => $brands,
            'search' => $search,
        ];
    }

    public function show(Brand $brand, array $filters = []): array
    {
        $brand->loadCount(['products' => fn (Builder $query) => $query->published()]);

        $filtersWithBrand = $filters;
        $filtersWithBrand['brand'] = $brand->getKey();

        $products = $this->products->getProductsForBrand($brand, $filters);

        $availableCategories = $this->resolveBrandCategories($brand);

        return [
            'brand' => $brand,
            'products' => $products,
            'availableSorts' => $this->products->sortOptions(),
            'availableFilters' => $this->products->filterOptions(),
            'activeSort' => $this->products->resolveSortKey($filters['sort'] ?? null),
            'activeFilter' => $filters['filter'] ?? null,
            'availableCategories' => $availableCategories,
            'relatedCategories' => $availableCategories,
            'filters' => $filtersWithBrand,
        ];
    }

    private function resolveBrandCategories(Brand $brand): Collection
    {
        return Category::query()
            ->whereHas('products', function (Builder $query) use ($brand): void {
                $query->published()->where('brand_id', $brand->getKey());
            })
            ->withCount(['products as published_products_count' => function (Builder $builder) use ($brand): void {
                $builder->published()->where('brand_id', $brand->getKey());
            }])
            ->orderByDesc('published_products_count')
            ->limit(6)
            ->get();
    }
}
