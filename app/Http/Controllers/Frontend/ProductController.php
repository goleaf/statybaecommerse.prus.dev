<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use function now;

final class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'search' => (string) $request->query('q', ''),
            'category' => (string) $request->query('category', ''),
            'brand' => (string) $request->query('brand', ''),
        ];

        $products = $this->buildProductQuery($filters)
            ->with(['brand', 'categories', 'media', 'prices.currency'])
            ->paginate(12)
            ->withQueryString();

        return view('frontend.products.index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => $this->loadCategories(),
            'brands' => $this->loadBrands(),
        ]);
    }

    public function search(Request $request): View
    {
        return $this->index($request);
    }

    public function byCategory(Request $request, Category $category): View
    {
        $request->merge(['category' => $category->slug]);

        return $this->index($request);
    }

    public function byBrand(Request $request, Brand $brand): View
    {
        $request->merge(['brand' => $brand->slug]);

        return $this->index($request);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_visible, 404);

        $product->load([
            'brand',
            'categories',
            'media',
            'prices.currency',
            'variants.media',
            'variants.prices.currency',
        ]);

        $reviews = $product->reviews()
            ->withoutGlobalScopes()
            ->where('is_approved', true)
            ->latest('created_at')
            ->limit(10)
            ->get();

        $relatedProducts = $this->loadRelatedProducts($product);

        return view('frontend.products.show', [
            'product' => $product,
            'reviews' => $reviews,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function addReview(Request $request, Product $product): RedirectResponse
    {
        abort_if(! Auth::check(), 403);

        $validator = Validator::make($request->all(), [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:2000'],
        ]);

        $validator->validate();

        Review::query()->withoutGlobalScopes()->updateOrCreate(
            [
                'product_id' => $product->id,
                'user_id' => Auth::id(),
            ],
            [
                'rating' => (int) $request->integer('rating'),
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'is_approved' => false,
                'locale' => app()->getLocale(),
            ],
        );

        return redirect()
            ->route('frontend.products.show', $product)
            ->with('status', __('Your review has been submitted and is pending approval.'));
    }

    private function buildProductQuery(array $filters)
    {
        return Product::query()
            ->withoutGlobalScopes()
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when($filters['search'], static function ($query, string $search): void {
                $like = '%' . $search . '%';
                $query->where(function ($inner) use ($like): void {
                    $inner->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('short_description', 'like', $like);
                });
            })
            ->when($filters['category'], static function ($query, string $category): void {
                $query->whereHas('categories', static function ($categoryQuery) use ($category): void {
                    $categoryQuery->where('slug', $category)->orWhere('id', $category);
                });
            })
            ->when($filters['brand'], static function ($query, string $brand): void {
                $query->whereHas('brand', static function ($brandQuery) use ($brand): void {
                    $brandQuery->where('slug', $brand)->orWhere('id', $brand);
                });
            })
            ->orderByDesc('published_at');
    }

    private function loadCategories(): Collection
    {
        return Category::query()
            ->withoutGlobalScopes()
            ->where('is_visible', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    private function loadBrands(): Collection
    {
        return Brand::query()
            ->withoutGlobalScopes()
            ->where('is_enabled', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    private function loadRelatedProducts(Product $product): Collection
    {
        return Product::query()
            ->withoutGlobalScopes()
            ->where('is_visible', true)
            ->where('id', '!=', $product->getKey())
            ->whereHas('categories', static function ($query) use ($product): void {
                $categoryIds = $product->categories->pluck('id');
                if ($categoryIds->isNotEmpty()) {
                    $query->whereIn('categories.id', $categoryIds);
                }
            })
            ->with(['media', 'prices.currency'])
            ->limit(4)
            ->get();
    }
}
