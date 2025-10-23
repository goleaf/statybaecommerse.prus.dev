<?php

declare(strict_types=1);

namespace App\Services\Frontend;

use App\Models\Brand;
use App\Services\Shared\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class BrandShowcaseDataProvider
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly ProductListingDataProvider $listingDataProvider,
    ) {}

    public function indexBrands(): Collection
    {
        return Brand::query()
            ->where('is_enabled', true)
            ->where('is_visible', true)
            ->orderBy('name')
            ->withCount('products')
            ->get();
    }

    public function loadBrand(Brand $brand): Brand
    {
        return $brand->loadMissing([
            'media',
            'translations' => static fn ($query) => $query->where('locale', app()->getLocale()),
        ]);
    }

    public function resolveFilters(Request $request): array
    {
        $filters = [];
        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $filters['search'] = $search;
        }

        $sortKey = (string) $request->query('sort', 'latest');
        $sort = $this->listingDataProvider->resolveSort($sortKey);
        $filters['sort_by'] = $sort['sort_by'];
        $filters['sort_direction'] = $sort['direction'];
        $filters['sort'] = $sortKey;

        return $filters;
    }

    public function products(Brand $brand, array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return $this->productService->getProductsByBrand($brand->id, $filters, $perPage);
    }

    public function breadcrumbs(Brand $brand): array
    {
        return [
            ['label' => __('Home'), 'url' => route('home')],
            ['label' => __('Brands'), 'url' => route('frontend.brands.index')],
            ['label' => $brand->name, 'url' => route('frontend.brands.show', $brand)],
        ];
    }

    public function availableSorts(): array
    {
        return $this->listingDataProvider->availableSorts();
    }
}
