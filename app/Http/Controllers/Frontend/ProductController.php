<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

final class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = $this->applyFilters($request, Product::query()->with(['brand', 'categories']))
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()->orderBy('name')->get();
        $brands = Brand::query()->orderBy('name')->get();

        return view('frontend.products.index', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'activeFilters' => $request->only(['search', 'category', 'brand', 'sort']),
        ]);
    }

    public function search(Request $request): View
    {
        $request->merge(['search' => $request->get('q')]);

        return $this->index($request);
    }

    public function byCategory(Request $request, Category $category): View
    {
        $products = $this->applyFilters(
            $request,
            Product::query()->with(['brand', 'categories'])->whereHas('categories', fn (Builder $query) => $query->whereKey($category->getKey()))
        )->paginate(12)->withQueryString();

        $brands = Brand::query()->orderBy('name')->get();

        return view('frontend.products.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(),
            'brands' => $brands,
            'activeFilters' => array_merge($request->only(['search', 'brand', 'sort']), ['category' => $category->slug]),
            'currentCategory' => $category,
        ]);
    }

    public function byBrand(Request $request, Brand $brand): View
    {
        $products = $this->applyFilters(
            $request,
            Product::query()->with(['brand', 'categories'])->where('brand_id', $brand->getKey())
        )->paginate(12)->withQueryString();

        return view('frontend.products.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(),
            'brands' => Brand::query()->orderBy('name')->get(),
            'activeFilters' => array_merge($request->only(['search', 'category', 'sort']), ['brand' => $brand->slug]),
            'currentBrand' => $brand,
        ]);
    }

    public function show(Product $product): View
    {
        $product->load(['brand', 'categories', 'reviews' => fn ($query) => $query->latest()->limit(10)]);

        $relatedProducts = Product::query()
            ->whereKeyNot($product->getKey())
            ->whereHas('categories', fn (Builder $query) => $query->whereIn('categories.id', $product->categories->pluck('id')))
            ->with('brand')
            ->limit(4)
            ->get();

        return view('frontend.products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function addReview(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        Review::create([
            'product_id' => $product->getKey(),
            'user_id' => $request->user()?->getKey(),
            'reviewer_name' => $request->user()?->name ?? $request->user()?->email ?? __('Guest'),
            'reviewer_email' => $request->user()?->email ?? null,
            'rating' => (int) $validated['rating'],
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'is_approved' => true,
            'locale' => app()->getLocale(),
        ]);

        return redirect()
            ->route('frontend.products.show', $product)
            ->with('status', 'review-added');
    }

    /**
     * Apply the supported filters to the provided query builder instance.
     */
    private function applyFilters(Request $request, Builder $query): Builder
    {
        $filters = $request->only(['search', 'category', 'brand', 'sort']);

        $query->when(Arr::get($filters, 'search'), function (Builder $builder, string $search): void {
            $builder->where(function (Builder $nested) use ($search): void {
                $nested
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        });

        $query->when(Arr::get($filters, 'category'), function (Builder $builder, string $categorySlug): void {
            $builder->whereHas('categories', fn (Builder $relation) => $relation->where('slug', $categorySlug));
        });

        $query->when(Arr::get($filters, 'brand'), function (Builder $builder, string $brandSlug): void {
            $builder->whereHas('brand', fn (Builder $relation) => $relation->where('slug', $brandSlug));
        });

        $query->when(Arr::get($filters, 'sort'), function (Builder $builder, string $sort): void {
            match ($sort) {
                'price-asc' => $builder->orderBy('price'),
                'price-desc' => $builder->orderByDesc('price'),
                'newest' => $builder->latest('created_at'),
                default => $builder->latest('published_at'),
            };
        }, fn (Builder $builder) => $builder->latest('published_at'));

        return $query;
    }
}
