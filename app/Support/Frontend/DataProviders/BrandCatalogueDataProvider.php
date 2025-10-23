<?php

declare(strict_types=1);

namespace App\Support\Frontend\DataProviders;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class BrandCatalogueDataProvider
{
    use BuildsProductCatalogueQuery;

    public function __construct(private readonly ProductCatalogueDataProvider $products) {}

    public function index(): array
    {
        $brands = Brand::query()
            ->withCount([
                'products as visible_products_count' => static function (Builder $query): void {
                    $query->where('is_visible', true)
                        ->whereNotNull('published_at')
                        ->where('published_at', '<=', now());
                },
            ])
            ->orderBy('name')
            ->get();

        return [
            'brands' => $brands,
        ];
    }

    public function show(Brand $brand, array $filters = []): array
    {
        $brand->loadCount(['products' => fn (Builder $query) => $query->published()]);

        $products = $this->products->getProductsForBrand($brand, $filters);

        return [
            'brand' => $brand,
            'products' => $products,
            'availableSorts' => $this->products->sortOptions(),
            'availableFilters' => $this->products->filterOptions(),
            'activeSort' => $this->products->resolveSortKey($filters['sort'] ?? null),
            'activeFilter' => $filters['filter'] ?? null,
            'relatedCategories' => $this->resolveBrandCategories($brand),
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
