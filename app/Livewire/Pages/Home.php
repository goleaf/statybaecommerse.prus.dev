<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

final class Home extends Component
{
    use WithCart, WithNotifications;

    public function getFeaturedProductsProperty(): Collection
    {
        return Product::query()
            ->with(['brand', 'categories', 'media'])
            ->where('is_visible', true)
            ->where('is_featured', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
    }

    public function getLatestProductsProperty(): Collection
    {
        return Product::query()
            ->with(['brand', 'categories', 'media'])
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();
    }

    public function getPopularProductsProperty(): Collection
    {
        return Product::query()
            ->with(['brand', 'categories', 'media'])
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('reviews')
            ->withCount('reviews')
            ->orderBy('reviews_count', 'desc')
            ->limit(8)
            ->get();
    }

    public function getFeaturedCategoriesProperty(): Collection
    {
        return Category::query()
            ->with(['media'])
            ->where('is_visible', true)
            ->where('is_featured', true)
            ->whereHas('products', function ($query) {
                $query->where('is_visible', true);
            })
            ->withCount(['products' => function ($query) {
                $query->where('is_visible', true);
            }])
            ->orderBy('id')
            ->limit(6)
            ->get();
    }

    public function getFeaturedBrandsProperty(): Collection
    {
        return Brand::query()
            ->with(['media'])
            ->where('is_enabled', true)
            ->where('is_featured', true)
            ->whereHas('products', function ($query) {
                $query->where('is_visible', true);
            })
            ->withCount(['products' => function ($query) {
                $query->where('is_visible', true);
            }])
            ->orderBy('sort_order')
            ->limit(8)
            ->get();
    }

    public function getLatestReviewsProperty(): Collection
    {
        return Review::query()
            ->with(['product', 'user'])
            ->where('is_approved', true)
            ->whereHas('product', function ($query) {
                $query->where('is_visible', true);
            })
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();
    }

    public function getStatsProperty(): array
    {
        return [
            'products_count' => Product::where('is_visible', true)->count(),
            'categories_count' => Category::where('is_visible', true)->count(),
            'brands_count' => Brand::where('is_enabled', true)->count(),
            'reviews_count' => Review::where('is_approved', true)->count(),
            'avg_rating' => Review::where('is_approved', true)->avg('rating') ?? 0,
        ];
    }

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);

        if (! $product || ! $product->is_visible) {
            $this->notifyError(__('Product not found or not available'));

            return;
        }

        if ($product->stock_quantity <= 0) {
            $this->notifyError(__('Product is out of stock'));

            return;
        }

        $cartItems = session()->get('cart', []);

        if (isset($cartItems[$productId])) {
            $cartItems[$productId]['quantity']++;
        } else {
            $cartItems[$productId] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'image' => $product->getFirstMediaUrl('images'),
                'sku' => $product->sku,
            ];
        }

        session()->put('cart', $cartItems);

        $this->dispatch('cart-updated');
        $this->notifySuccess(__('Product added to cart'));
    }

    public function render()
    {
        return view('livewire.pages.home', [
            'featuredProducts' => $this->featuredProducts,
            'latestProducts' => $this->latestProducts,
            'popularProducts' => $this->popularProducts,
            'featuredCategories' => $this->featuredCategories,
            'featuredBrands' => $this->featuredBrands,
            'latestReviews' => $this->latestReviews,
            'stats' => $this->stats,
        ])->layout('components.layouts.base', [
            'title' => __('Home').' - '.config('app.name'),
        ]);
    }
}
