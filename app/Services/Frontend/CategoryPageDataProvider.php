<?php

declare(strict_types=1);

namespace App\Services\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Services\Shared\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class CategoryPageDataProvider
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly ProductListingDataProvider $listingDataProvider,
    ) {}

    public function indexCategories(): Collection
    {
        return Category::query()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount('products')
            ->get();
    }

    public function loadCategory(Category $category): Category
    {
        if (! $category->is_visible) {
            abort(404);
        }

        return $category->loadMissing([
            'media',
            'translations' => static fn ($query) => $query->where('locale', app()->getLocale()),
            'parent',
            'parent.translations' => static fn ($query) => $query->where('locale', app()->getLocale()),
        ]);
    }

    public function childCategories(Category $category): Collection
    {
        return $category->children()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount('products')
            ->get();
    }

    public function resolveFilters(Request $request): array
    {
        $filters = [];
        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $filters['search'] = $search;
        }

        $brandSlug = $request->query('brand');
        if ($brandSlug) {
            $brand = Brand::query()->where('slug', $brandSlug)->first();
            if ($brand) {
                $filters['brands'] = [$brand->id];
            }
        }

        $sortKey = (string) $request->query('sort', 'latest');
        $sort = $this->listingDataProvider->resolveSort($sortKey);
        $filters['sort_by'] = $sort['sort_by'];
        $filters['sort_direction'] = $sort['direction'];
        $filters['sort'] = $sortKey;

        return $filters;
    }

    public function products(Category $category, array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return $this->productService->getProductsByCategory($category->id, $filters, $perPage);
    }

    public function breadcrumbs(Category $category): array
    {
        $breadcrumbs = [
            ['label' => __('Home'), 'url' => route('home')],
            ['label' => __('Categories'), 'url' => route('frontend.categories.index')],
        ];

        $ancestors = collect();
        $current = $category->parent;
        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        foreach ($ancestors->reverse() as $ancestor) {
            $breadcrumbs[] = [
                'label' => $ancestor->name,
                'url' => route('frontend.categories.show', $ancestor),
            ];
        }

        $breadcrumbs[] = [
            'label' => $category->name,
            'url' => route('frontend.categories.show', $category),
        ];

        return $breadcrumbs;
    }

    public function availableSorts(): array
    {
        return $this->listingDataProvider->availableSorts();
    }

    public function brands(): Collection
    {
        return $this->listingDataProvider->brands();
    }
}
