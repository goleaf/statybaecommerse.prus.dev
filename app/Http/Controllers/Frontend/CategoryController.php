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

final class CategoryController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(): View
    {
        return view('categories.index', [
            'categories' => $this->categoryTree(),
        ]);
    }

    public function show(Request $request, Category $category): View
    {
        $category->load(['translations', 'children' => fn ($query) => $query->ordered()]);

        $filters = [
            'search' => trim((string) $request->input('q', '')),
            'brands' => $this->normalizeFilterValues($request->input('brand')),
            'sort' => $request->input('sort', 'latest'),
        ];

        [$sortBy, $direction] = $this->resolveSort($filters['sort']);

        $brandIds = $this->resolveBrandIds($filters['brands']);

        $products = $this->productService->getProductsByCategory($category->id, [
            'search' => $filters['search'] !== '' ? $filters['search'] : null,
            'brands' => $brandIds,
            'sort_by' => $sortBy,
            'sort_direction' => $direction,
        ], 12);

        $products->appends($request->query());

        return view('categories.show', [
            'category' => $category,
            'products' => $products,
            'filters' => $filters,
            'availableBrands' => $this->brandOptions(),
        ]);
    }

    private function categoryTree(): Collection
    {
        return Category::query()
            ->roots()
            ->ordered()
            ->with('children')
            ->get();
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

    private function resolveBrandIds(array $values): array
    {
        $collection = collect($values);

        if ($collection->isEmpty()) {
            return [];
        }

        [$idValues, $slugValues] = $collection->partition(fn ($value) => is_numeric($value));

        $ids = Brand::query()->whereIn('id', $idValues->all())->pluck('id')->all();

        if ($slugValues->isNotEmpty()) {
            $ids = array_merge($ids, Brand::query()->whereIn('slug', $slugValues->all())->pluck('id')->all());
        }

        return array_values(array_unique($ids));
    }

    private function brandOptions(): Collection
    {
        return Brand::query()
            ->whereHas('products', fn (Builder $query) => $query->published())
            ->orderBy('name')
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
