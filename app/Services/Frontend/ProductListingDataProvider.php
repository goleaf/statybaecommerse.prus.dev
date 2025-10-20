<?php

declare(strict_types=1);

namespace App\Services\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\Shared\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class ProductListingDataProvider
{
    private const SORTS = [
        'latest' => ['label' => 'Newest arrivals', 'sort_by' => 'created_at', 'direction' => 'desc'],
        'name' => ['label' => 'Name A-Z', 'sort_by' => 'name', 'direction' => 'asc'],
        'price_asc' => ['label' => 'Price: Low to High', 'sort_by' => 'price', 'direction' => 'asc'],
        'price_desc' => ['label' => 'Price: High to Low', 'sort_by' => 'price', 'direction' => 'desc'],
        'popular' => ['label' => 'Most popular', 'sort_by' => 'popularity', 'direction' => 'desc'],
        'rating' => ['label' => 'Best rated', 'sort_by' => 'rating', 'direction' => 'desc'],
    ];

    public function __construct(private readonly ProductService $productService) {}

    public function resolveFilters(Request $request): array
    {
        $filters = [];
        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $filters['search'] = $search;
        }

        $categorySlug = $request->query('category');
        if ($categorySlug) {
            $category = Category::query()->where('slug', $categorySlug)->first();
            if ($category) {
                $filters['categories'] = [$category->id];
            }
        }

        $brandSlug = $request->query('brand');
        if ($brandSlug) {
            $brand = Brand::query()->where('slug', $brandSlug)->first();
            if ($brand) {
                $filters['brands'] = [$brand->id];
            }
        }

        $sortKey = (string) $request->query('sort', 'latest');
        $sort = $this->resolveSort($sortKey);
        $filters['sort_by'] = $sort['sort_by'];
        $filters['sort_direction'] = $sort['direction'];
        $filters['sort'] = $sortKey;

        return $filters;
    }

    public function paginatedProducts(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return $this->productService->searchProducts($filters, $perPage);
    }

    public function availableSorts(): array
    {
        return collect(self::SORTS)
            ->mapWithKeys(static fn (array $config, string $key) => [$key => $config['label']])
            ->all();
    }

    public function categories(): Collection
    {
        return Category::query()
            ->where('is_visible', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    public function brands(): Collection
    {
        return Brand::query()
            ->where('is_enabled', true)
            ->where('is_visible', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    public function loadProduct(Product $product): Product
    {
        return $product->loadMissing([
            'brand',
            'media',
            'categories',
            'prices.currency',
            'translations' => static fn ($query) => $query->where('locale', app()->getLocale()),
            'reviews' => static fn ($query) => $query->where('is_approved', true)->latest('created_at')->limit(5),
        ]);
    }

    public function relatedProducts(Product $product, int $limit = 4): Collection
    {
        return $this->productService->getRelatedProducts($product, $limit);
    }

    public function resolveSort(string $key): array
    {
        return self::SORTS[$key] ?? self::SORTS['latest'];
    }
}
