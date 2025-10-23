<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Repositories\ProductRepository;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTags;
use App\Support\Cache\TagAwareCache;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read array<string, int|float> $stats
 * @property-read Collection<int, Product> $featuredProducts
 * @property-read Collection<int, Product> $latestProducts
 * @property-read Collection<int, Review> $latestReviews
 */
final class Home extends Component
{
    use WithCart;
    use WithNotifications;

    /**
     * @return array<string, int|float>
     */
    #[Computed]
    public function stats(): array
    {
        $locale = app()->getLocale();

        return TagAwareCache::remember(
            CacheKeys::homeStats($locale),
            now()->addSeconds(60),
            function (): array {
                return [
                    'products_count' => Product::query()->where('is_visible', true)->count(),
                    'categories_count' => Category::query()->where('is_visible', true)->count(),
                    'brands_count' => Brand::query()->where('is_enabled', true)->count(),
                    'reviews_count' => Review::query()->where('is_approved', true)->count(),
                    'avg_rating' => (float) (Review::query()->where('is_approved', true)->avg('rating') ?? 0),
                ];
            },
            [
                CacheTags::home(),
                CacheTags::locale($locale),
                CacheTags::products(),
                CacheTags::categories(),
                CacheTags::brands(),
                CacheTags::reviews(),
                CacheKeys::productAggregateTag(),
            ]
        );
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function featuredProducts(): Collection
    {
        $locale = app()->getLocale();

        return TagAwareCache::remember(
            CacheKeys::homeFeaturedProducts($locale),
            now()->addSeconds(60),
            static function (): Collection {
                return Product::query()
                    ->withoutGlobalScopes()
                    ->where('is_visible', true)
                    ->where('is_featured', true)
                    ->latest('published_at')
                    ->limit(8)
                    ->get();
            },
            [
                CacheTags::home(),
                CacheTags::locale($locale),
                CacheTags::products(),
                CacheTags::brands(),
                CacheTags::categories(),
                CacheKeys::homeTag(),
            ]
        );
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function latestProducts(): Collection
    {
        $locale = app()->getLocale();

        return TagAwareCache::remember(
            CacheKeys::homeLatestProducts($locale),
            now()->addSeconds(60),
            static function (): Collection {
                return Product::query()
                    ->withoutGlobalScopes()
                    ->where('is_visible', true)
                    ->latest('published_at')
                    ->limit(8)
                    ->get();
            },
            [
                CacheTags::home(),
                CacheTags::locale($locale),
                CacheTags::products(),
                CacheTags::brands(),
                CacheTags::categories(),
                CacheKeys::homeTag(),
            ]
        );
    }

    /**
     * @return Collection<int, Review>
     */
    #[Computed]
    public function latestReviews(): Collection
    {
        $locale = app()->getLocale();

        return TagAwareCache::remember(
            CacheKeys::homeLatestReviews($locale),
            now()->addSeconds(60),
            static function (): Collection {
                return Review::query()
                    ->where('is_approved', true)
                    ->with(['product' => static fn ($query) => $query->select('id', 'name', 'slug')])
                    ->latest('created_at')
                    ->limit(6)
                    ->get();
            },
            [
                CacheTags::home(),
                CacheTags::locale($locale),
                CacheTags::reviews(),
                CacheTags::products(),
                CacheKeys::homeTag(),
            ]
        );
    }

    public function addToCart(int $productId): void
    {
        $product = app(ProductRepository::class)->findPublishedById($productId);

        if (! $product || ($product->stock_quantity ?? 0) < 1) {
            $this->notifyWarning(__('This product is currently unavailable.'));

            return;
        }

        $this->persistCartItem($product);
    }

    public function render(): View
    {
        $appName = config('app.name');

        return view('livewire.pages.home', [
            'stats' => $this->stats,
            'featuredProducts' => $this->featuredProducts,
            'latestProducts' => $this->latestProducts,
            'latestReviews' => $this->latestReviews,
        ])->layout('components.layouts.base', [
            'title' => __('frontend.navigation.home').' - '.(is_string($appName) ? $appName : ''),
        ]);
    }
}
