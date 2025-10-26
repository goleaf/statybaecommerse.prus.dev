<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Data\Storefront\Home\HomeStatsData;
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
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read HomeStatsData $stats
 * @property-read Collection<int, Product> $featuredProducts
 * @property-read Collection<int, Product> $latestProducts
 * @property-read Collection<int, Review> $latestReviews
 */
final class Home extends Component
{
    use WithCart;
    use WithNotifications;

    #[Computed]
    public function stats(): HomeStatsData
    {
        $locale = app()->getLocale();

        /** @var array<string, int|float> $stats */
        $stats = TagAwareCache::remember(
            CacheKeys::homeStats($locale),
            now()->addSeconds(60),
            static fn (): array => [
                'products_count'   => Product::query()->where('is_visible', true)->count(),
                'categories_count' => Category::query()->where('is_visible', true)->count(),
                'brands_count'     => Brand::query()->where('is_enabled', true)->count(),
                'reviews_count'    => Review::query()->where('is_approved', true)->count(),
                'avg_rating'       => (float) (Review::query()->where('is_approved', true)->avg('rating') ?? 0),
            ],
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

        return HomeStatsData::fromArray($stats);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    #[Computed]
    public function featuredProducts(): EloquentCollection
    {
        $locale = app()->getLocale();

        /** @var EloquentCollection<int, Product> $products */
        $products = TagAwareCache::remember(
            CacheKeys::homeFeaturedProducts($locale),
            now()->addSeconds(60),
            static fn (): EloquentCollection => // Only highlight products that meet the minimum data quality bar for the storefront hero rail.
                Product::query()
                    ->withoutGlobalScopes()
                    ->where('is_visible', true)
                    ->where('is_featured', true)
                    ->whereNotNull('name')
                    ->where('name', '!=', '')
                    ->whereNotNull('slug')
                    ->where('slug', '!=', '')
                    ->whereNotNull('price')
                    ->where('price', '>', 0)
                    ->latest('published_at')
                    ->limit(8)
                    ->get(),
            [
                CacheTags::home(),
                CacheTags::locale($locale),
                CacheTags::products(),
                CacheTags::brands(),
                CacheTags::categories(),
                CacheKeys::homeTag(),
            ]
        );

        return $products;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    #[Computed]
    public function latestProducts(): EloquentCollection
    {
        $locale = app()->getLocale();

        /** @var EloquentCollection<int, Product> $products */
        $products = TagAwareCache::remember(
            CacheKeys::homeLatestProducts($locale),
            now()->addSeconds(60),
            static fn (): EloquentCollection => // Keep the "New arrivals" carousel consistent by requiring basic merchandising fields.
                Product::query()
                    ->withoutGlobalScopes()
                    ->where('is_visible', true)
                    ->whereNotNull('name')
                    ->where('name', '!=', '')
                    ->whereNotNull('slug')
                    ->where('slug', '!=', '')
                    ->whereNotNull('price')
                    ->where('price', '>', 0)
                    ->latest('published_at')
                    ->limit(8)
                    ->get(),
            [
                CacheTags::home(),
                CacheTags::locale($locale),
                CacheTags::products(),
                CacheTags::brands(),
                CacheTags::categories(),
                CacheKeys::homeTag(),
            ]
        );

        return $products;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Review>
     */
    #[Computed]
    public function latestReviews(): EloquentCollection
    {
        $locale = app()->getLocale();

        /** @var EloquentCollection<int, Review> $reviews */
        $reviews = TagAwareCache::remember(
            CacheKeys::homeLatestReviews($locale),
            now()->addSeconds(60),
            static fn (): EloquentCollection => // Restrict reviews to those with a resolvable product relationship for consistent rendering.
                Review::query()
                    ->where('is_approved', true)
                    ->with(['product:id,name,slug'])
                    ->latest('created_at')
                    ->limit(6)
                    ->get(),
            [
                CacheTags::home(),
                CacheTags::locale($locale),
                CacheTags::reviews(),
                CacheTags::products(),
                CacheKeys::homeTag(),
            ]
        );

        return $reviews;
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
            // Pass primitive array to Blade while keeping typed data internally for reuse.
            'stats'            => $this->stats->toArray(),
            'featuredProducts' => $this->featuredProducts,
            'latestProducts'   => $this->latestProducts,
            'latestReviews'    => $this->latestReviews,
        ])->layout('components.layouts.base', [
            'title' => __('frontend.navigation.home') . ' - ' . (is_string($appName) ? $appName : ''),
        ]);

        // Return the typed view instance so PHPStan recognises the response contract.
        return $view;
    }
}
