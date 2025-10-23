<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->with(['brand', 'media'])
            ->withCount('reviews');

        $filters = $this->extractFilters($request);

        if ($filters['search']) {
            $query->where(function ($innerQuery) use ($filters): void {
                $innerQuery
                    ->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('slug', 'like', '%'.$filters['search'].'%');
            });
        }

        if ($filters['category']) {
            $query->whereHas('categories', function ($innerQuery) use ($filters): void {
                $innerQuery->where('slug', $filters['category']);
            });
        }

        if ($filters['brand']) {
            $query->whereHas('brand', function ($innerQuery) use ($filters): void {
                $innerQuery->where('slug', $filters['brand']);
            });
        }

        $this->applySorting($query, $filters['sort']);

        /** @var LengthAwarePaginator $products */
        $products = $query->paginate(12)->withQueryString();

        return view('frontend.products.index', [
            'products' => $products,
            'filters' => $filters,
            'brands' => $this->loadBrands(),
            'categories' => $this->loadCategories(),
            'currentCategory' => $this->resolveCurrentCategory($filters['category']),
            'currentBrand' => $this->resolveCurrentBrand($filters['brand']),
        ]);
    }

    public function show(Product $product): View
    {
        $product->load([
            'brand',
            'categories',
            'media',
            'variants.media',
        ]);

        $relatedProducts = $product->categories()
            ->with(['products' => fn ($query) => $query->with('brand')->where('products.id', '!=', $product->id)->limit(8)])
            ->get()
            ->pluck('products')
            ->flatten()
            ->unique('id')
            ->take(8);

        $reviews = $product->reviews()
            ->withoutGlobalScopes()
            ->latest()
            ->take(5)
            ->get();

        return view('frontend.products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
        ]);
    }

    public function search(Request $request): View
    {
        if ($request->filled('q') && ! $request->filled('search')) {
            $request->merge(['search' => $request->input('q')]);
        }

        return $this->index($request);
    }

    public function byCategory(Category $category, Request $request): View
    {
        $request->merge(['category' => $category->slug]);

        return $this->index($request);
    }

    public function byBrand(Brand $brand, Request $request): View
    {
        $request->merge(['brand' => $brand->slug]);

        return $this->index($request);
    }

    public function addReview(Product $product, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        $reviewAttributes = [
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'content' => $data['content'] ?? null,
            'reviewer_name' => $request->user()?->name ?? $request->input('name', 'Guest'),
            'reviewer_email' => $request->user()?->email ?? $request->input('email', 'guest@example.com'),
            'is_approved' => true,
        ];

        if ($request->user()) {
            $reviewAttributes['user_id'] = $request->user()->id;
        }

        $product->reviews()->create($reviewAttributes);

        return redirect()
            ->route('frontend.products.show', $product)
            ->with('status', __('Thank you for reviewing :product', ['product' => $product->name]));
    }

    private function extractFilters(Request $request): array
    {
        return [
            'search' => Str::of((string) $request->input('search'))->trim()->whenEmpty(fn () => null)->toString(),
            'category' => Str::of((string) $request->input('category'))->trim()->whenEmpty(fn () => null)->toString(),
            'brand' => Str::of((string) $request->input('brand'))->trim()->whenEmpty(fn () => null)->toString(),
            'sort' => $request->input('sort', 'latest'),
        ];
    }

    private function applySorting($query, string $sort): void
    {
        $direction = 'desc';

        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price');
                break;
            case 'price_desc':
                $query->orderByDesc('price');
                break;
            case 'name_asc':
                $query->orderBy('name');
                break;
            case 'name_desc':
                $query->orderByDesc('name');
                break;
            default:
                $query->orderBy('created_at', $direction);
                break;
        }
    }

    private function loadBrands(): Collection
    {
        return Brand::query()
            ->select(['id', 'name', 'slug'])
            ->orderBy('name')
            ->get();
    }

    private function loadCategories(): Collection
    {
        return Category::query()
            ->select(['id', 'name', 'slug'])
            ->orderBy('name')
            ->get();
    }

    private function resolveCurrentCategory(?string $slug): ?Category
    {
        if (! $slug) {
            return null;
        }

        return Category::query()->where('slug', $slug)->first();
    }

    private function resolveCurrentBrand(?string $slug): ?Brand
    {
        if (! $slug) {
            return null;
        }

        return Brand::query()->where('slug', $slug)->first();
    }
}
