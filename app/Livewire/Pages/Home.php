<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Support\Cache\CacheKeys;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Home extends Component
{
    use WithCart;
    use WithNotifications;

    #[Computed]
    public function stats(): array
    {
        $locale = app()->getLocale();

        return Cache::remember(CacheKeys::homeStats($locale), CacheKeys::TTL_MINUTE, function (): array {
            return [
                'products_count' => Product::where('is_visible', true)->count(),
                'categories_count' => Category::where('is_visible', true)->count(),
                'brands_count' => Brand::where('is_enabled', true)->count(),
                'reviews_count' => Review::where('is_approved', true)->count(),
                'avg_rating' => (float) (Review::where('is_approved', true)->avg('rating') ?? 0),
            ];
        });
    }

    #[Computed]
    public function featuredProducts(): Collection
    {
        $locale = app()->getLocale();

        return Cache::remember(CacheKeys::homeFeaturedProducts($locale), CacheKeys::TTL_MINUTE, static function (): Collection {
            return Product::query()
                ->withoutGlobalScopes()
                ->where('is_visible', true)
                ->where('is_featured', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->latest('published_at')
                ->limit(8)
                ->get();
        });
    }

    #[Computed]
    public function latestProducts(): Collection
    {
        $locale = app()->getLocale();

        return Cache::remember(CacheKeys::homeLatestProducts($locale), CacheKeys::TTL_MINUTE, static function (): Collection {
            return Product::query()
                ->withoutGlobalScopes()
                ->where('is_visible', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->latest('created_at')
                ->limit(8)
                ->get();
        });
    }

    #[Computed]
    public function latestReviews(): Collection
    {
        $locale = app()->getLocale();

        return Cache::remember(CacheKeys::homeLatestReviews($locale), CacheKeys::TTL_MINUTE, static function (): Collection {
            return Review::query()
                ->where('is_approved', true)
                ->with(['product' => static fn ($query) => $query->select('id', 'name', 'slug')])
                ->latest('created_at')
                ->limit(6)
                ->get();
        });
    }

    public function addToCart(int $productId): void
    {
        $product = Product::query()
            ->withoutGlobalScopes()
            ->whereKey($productId)
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->first();

        if (! $product || ($product->stock_quantity ?? 0) < 1) {
            $this->notifyWarning(__('This product is currently unavailable.'));

            return;
        }

        $this->persistCartItem($product);
    }

    public function render()
    {
        return view('livewire.pages.home', [
            'stats' => $this->stats,
            'featuredProducts' => $this->featuredProducts,
            'latestProducts' => $this->latestProducts,
            'latestReviews' => $this->latestReviews,
        ])->layout('components.layouts.base', [
            'title' => __('frontend.navigation.home').' - '.config('app.name'),
        ]);
    }
}
