<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Services\Shared\ProductService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

final class BrandController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));

        $brands = Brand::query()
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
            ->withCount(['products as published_products_count' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->paginate(18);

        $brands->appends($request->query());

        return view('brands.index', [
            'brands' => $brands,
            'search' => $search,
        ]);
    }

    public function show(Request $request, Brand $brand): View
    {
        $brand->load('translations');

        $filters = [
            'search' => trim((string) $request->input('q', '')),
            'categories' => $this->normalizeFilterValues($request->input('category')),
            'sort' => $request->input('sort', 'latest'),
        ];

        [$sortBy, $direction] = $this->resolveSort($filters['sort']);

        $categoryIds = $this->resolveCategoryIds($filters['categories']);

        $products = $this->productService->getProductsByBrand($brand->id, [
            'search' => $filters['search'] !== '' ? $filters['search'] : null,
            'categories' => $categoryIds,
            'sort_by' => $sortBy,
            'sort_direction' => $direction,
        ], 12);

        $products->appends($request->query());

        return view('brands.show', [
            'brand' => $brand,
            'products' => $products,
            'filters' => $filters,
            'availableCategories' => $this->categoryOptions(),
        ]);
    }

    private function normalizeFilterValues(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        return collect(is_array($value) ? $value : [$value])
            ->map(fn ($item) => is_string($item) ? trim($item) : $item)
            ->filter(fn ($item) => $item !== '' && $item !== null)
            ->values()
            ->all();
    }

    private function resolveCategoryIds(array $values): array
    {
        $collection = collect($values);

        if ($collection->isEmpty()) {
            return [];
        }

        [$idValues, $slugValues] = $collection->partition(fn ($value) => is_numeric($value));

        $ids = Category::query()->whereIn('id', $idValues->all())->pluck('id')->all();

        if ($slugValues->isNotEmpty()) {
            $ids = array_merge($ids, Category::query()->whereIn('slug', $slugValues->all())->pluck('id')->all());
        }

        return array_values(array_unique($ids));
    }

    private function categoryOptions(): Collection
    {
        return Category::query()
            ->roots()
            ->ordered()
            ->with('children')
            ->get();
    }

    private function resolveSort(string $sort): array
    {
        return match ($sort) {
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            default => ['published_at', 'desc'],
        };
    }
}
