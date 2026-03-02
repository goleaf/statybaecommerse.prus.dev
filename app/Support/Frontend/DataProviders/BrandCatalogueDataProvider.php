<?php

declare(strict_types=1);

namespace App\Support\Frontend\DataProviders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
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
        $categoryProductSections = collect();
        if ($products->isEmpty() && $availableCategories->isNotEmpty()) {
            $categoryProductSections = $this->buildCategoryProductSections($brand, $availableCategories);
        }

        return [
            'brand'               => $brand,
            'products'            => $products,
            'availableSorts'      => $this->products->sortOptions(),
            'availableFilters'    => $this->products->filterOptions(),
            'activeSort'          => $this->products->resolveSortKey($filters['sort'] ?? null),
            'activeFilter'        => $filters['filter'] ?? null,
            'availableCategories' => $availableCategories,
            'relatedCategories'   => $availableCategories,
            'categoryProductSections' => $categoryProductSections,
            'filters'             => $filtersWithBrand,
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
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, array{category: Category, products: Collection<int, Product>}>
     */
    private function buildCategoryProductSections(Brand $brand, Collection $categories): Collection
    {
        return $categories
            ->map(function (Category $category) use ($brand): array {
                $products = Product::query()
                    ->with(['brand', 'media', 'primaryImage'])
                    ->published()
                    ->enabled()
                    ->where('brand_id', $brand->getKey())
                    ->whereHas('categories', static function (Builder $query) use ($category): void {
                        $query->where('categories.id', $category->getKey());
                    })
                    ->orderByDesc('created_at')
                    ->limit(8)
                    ->get();

                return [
                    'category' => $category,
                    'products' => $products,
                ];
            })
            ->filter(static fn (array $section): bool => $section['products']->isNotEmpty())
            ->values();
    }
}
