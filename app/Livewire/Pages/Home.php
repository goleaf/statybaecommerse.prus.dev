<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Data\Storefront\Home\HomeStatsData;
use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTags;
use App\Support\Cache\TagAwareCache;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read HomeStatsData $stats
 * @property-read Collection<int, Product> $featuredProducts
 * @property-read Collection<int, Product> $latestProducts
 */
final class Home extends Component
{
    use WithCart;
    use WithNotifications;

    public function mount(): void
    {
        // Mount method can be empty or contain other initialization logic
    }

    #[Computed]
    public function stats(): HomeStatsData
    {
        $locale = app()->getLocale();

        /** @var array<string, int|float> $stats */
        $stats = TagAwareCache::remember(
            CacheKeys::homeStats($locale),
            now()->addSeconds(60),
            static function (): array {
                return [
                    'products_count'   => Product::query()->where('is_visible', true)->count(),
                    'categories_count' => Category::query()->where('is_visible', true)->count(),
                    'brands_count'     => Brand::query()->where('is_enabled', true)->count(),
                    'reviews_count'    => 0,
                    'avg_rating'       => 0.0,
                ];
            },
            [
                CacheTags::home(),
                CacheTags::locale($locale),
                CacheTags::products(),
                CacheTags::categories(),
                CacheTags::brands(),
                CacheKeys::productAggregateTag(),
            ]
        );

        return HomeStatsData::fromArray($stats);
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function featuredProducts(): Collection
    {
        $locale = app()->getLocale();

        /** @var Collection<int, Product> $products */
        $products = TagAwareCache::remember(
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

        return $products;
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function latestProducts(): Collection
    {
        $locale = app()->getLocale();

        /** @var Collection<int, Product> $products */
        $products = TagAwareCache::remember(
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

        return $products;
    }

    public function render(): View
    {
        $appName = config('app.name');

        return view('livewire.pages.home', [
            // Pass primitive array to Blade while keeping typed data internally for reuse.
            'stats'            => $this->stats->toArray(),
            'featuredProducts' => $this->featuredProducts,
            'latestProducts'   => $this->latestProducts,
        ])->layout('components.layouts.base', [
            'title' => __('messages.home_homepage') . ' - ' . (is_string($appName) ? $appName : ''),
        ]);
    }
}
