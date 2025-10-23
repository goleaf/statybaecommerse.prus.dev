<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Services\Shared\ProductService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

final class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): View
    {
        $filters = $this->extractFilters($request);
        $filtersForService = $this->mapFiltersForService($filters);

        $products = $this->productService->searchProducts($filtersForService, 12);
        $products->appends($request->query());

        return view('products.index', [
            'products' => $products,
            'filters' => $filters,
            'availableCategories' => $this->categoryOptions(),
            'availableBrands' => $this->brandOptions(),
            'pageTitle' => __('Product catalogue'),
            'context' => 'catalogue',
        ]);
    }

    public function search(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $filters = $this->extractFilters($request);
        $filtersForService = $this->mapFiltersForService($filters);

        $products = $this->productService->searchProducts($filtersForService, 12);
        $products->appends($request->query());

        return view('products.index', [
            'products' => $products,
            'filters' => $filters,
            'availableCategories' => $this->categoryOptions(),
            'availableBrands' => $this->brandOptions(),
            'pageTitle' => __('Search results'),
            'context' => 'search',
        ]);
    }

    public function byCategory(Request $request, Category $category): View
    {
        $category->load(['translations', 'children' => fn ($query) => $query->ordered()]);

        $filters = $this->extractFilters($request);
        $filtersForService = $this->mapFiltersForService($filters);

        $products = $this->productService->getProductsByCategory($category->id, $filtersForService, 12);
        $products->appends($request->query());

        return view('categories.show', [
            'category' => $category,
            'products' => $products,
            'filters' => $filters,
            'availableBrands' => $this->brandOptions(),
        ]);
    }

    public function byBrand(Request $request, Brand $brand): View
    {
        $brand->load('translations');

        $filters = $this->extractFilters($request);
        $filtersForService = $this->mapFiltersForService($filters);

        $products = $this->productService->getProductsByBrand($brand->id, $filtersForService, 12);
        $products->appends($request->query());

        return view('brands.show', [
            'brand' => $brand,
            'products' => $products,
            'filters' => $filters,
            'availableCategories' => $this->categoryOptions(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->isPublished(), 404);

        $product = Product::query()
            ->whereKey($product)
            ->with(['brand.translations', 'categories.translations', 'translations'])
            ->firstOrFail();

        return view('products.show', [
            'product' => $product,
        ]);
    }

    public function addReview(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->isPublished(), 404);

        $rules = [
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
            'reviewer_name' => ['nullable', 'string', 'max:255'],
            'reviewer_email' => ['nullable', 'email', 'max:255'],
        ];

        if (! $request->user()) {
            $rules['reviewer_name'][0] = 'required';
            $rules['reviewer_email'][0] = 'required';
        }

        $validated = $request->validate($rules);

        $user = $request->user();

        Review::query()->create([
            'product_id' => $product->id,
            'user_id' => $user?->id,
            'reviewer_name' => $validated['reviewer_name'] ?? $user?->name ?? 'Guest',
            'reviewer_email' => $validated['reviewer_email'] ?? $user?->email ?? 'guest@example.com',
            'rating' => (int) $validated['rating'],
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'is_approved' => false,
            'locale' => app()->getLocale(),
        ]);

        return redirect()
            ->route('frontend.products.show', $product)
            ->with('status', __('Your review has been submitted and will appear once approved.'));
    }

    private function extractFilters(Request $request): array
    {
        return [
            'search' => trim((string) $request->input('q', '')),
            'categories' => $this->normalizeFilterValues($request->input('category')),
            'brands' => $this->normalizeFilterValues($request->input('brand')),
            'sort' => $request->input('sort', 'latest'),
        ];
    }

    private function mapFiltersForService(array $filters): array
    {
        [$sortBy, $direction] = $this->resolveSort($filters['sort']);

        return [
            'search' => $filters['search'] !== '' ? $filters['search'] : null,
            'categories' => $this->resolveCategoryIds($filters['categories']),
            'brands' => $this->resolveBrandIds($filters['brands']),
            'sort_by' => $sortBy,
            'sort_direction' => $direction,
        ];
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

    private function categoryOptions(): Collection
    {
        return Category::query()
            ->roots()
            ->ordered()
            ->with('children')
            ->get();
    }

    private function brandOptions(): Collection
    {
        return Brand::query()
            ->whereHas('products', fn (Builder $query) => $query->published())
            ->orderBy('name')
            ->get();
    }
}
