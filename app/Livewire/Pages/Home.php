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
use Illuminate\Support\Collection;
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

    public function boot(): void
    {
        // Ensure locale is set from route parameter on every request (including AJAX)
        // This must happen before any translations are used
        $this->ensureLocale();
    }

    public function mount(): void
    {
        // Ensure locale is set from route parameter
        $this->ensureLocale();
    }

    private function ensureLocale(): void
    {
        $request = request();

        // Get supported locales (config can be string or array)
        $supportedConfig = config('app.supported_locales', 'lt,en');
        $supportedLocales = [];
        if (is_array($supportedConfig)) {
            $supportedLocales = array_filter($supportedConfig, static fn ($locale): bool => is_string($locale) && $locale !== '');
        } elseif (is_string($supportedConfig)) {
            $supportedLocales = array_filter(
                array_map(
                    static fn (string $locale): string => trim($locale),
                    explode(',', $supportedConfig)
                ),
                static fn (string $locale): bool => $locale !== ''
            );
        }
        $supportedLocales = array_values(array_map(
            static fn (string $locale): string => trim($locale),
            $supportedLocales
        ));

        // Prefer locale from route parameter if present (e.g., /{locale}/...)
        $routeLocale = $request->route('locale');
        // Allow explicit override via query (?locale=xx)
        $queryLocale = $request->query('locale');

        // Get locale from query, header, session, cookie, or user preference
        $defaultLocaleConfig = config('app.locale', 'lt');
        $defaultLocale = is_string($defaultLocaleConfig) && $defaultLocaleConfig !== ''
            ? $defaultLocaleConfig
            : 'lt';

        $candidateLocales = array_values(array_filter([
            $routeLocale,
            $queryLocale,
            session('locale'),
            session('app.locale'),
            $request->cookie('app_locale'),
            auth()->check() ? (auth()->user()->preferred_locale ?? null) : null,
        ], static fn ($candidate): bool => is_string($candidate) && $candidate !== ''));

        $locale = $defaultLocale;

        foreach ($candidateLocales as $candidate) {
            if (in_array($candidate, $supportedLocales, true)) {
                $locale = $candidate;
                break;
            }
        }

        if (!in_array($locale, $supportedLocales, true)) {
            $fallbackLocaleConfig = config('app.fallback_locale');
            $fallbackLocale = is_string($fallbackLocaleConfig) && $fallbackLocaleConfig !== ''
                ? $fallbackLocaleConfig
                : $defaultLocale;

            if (in_array($fallbackLocale, $supportedLocales, true)) {
                $locale = $fallbackLocale;
            } elseif (in_array($defaultLocale, $supportedLocales, true)) {
                $locale = $defaultLocale;
            } elseif ($supportedLocales !== []) {
                $locale = $supportedLocales[0];
            } else {
                $locale = $defaultLocale;
            }
        }

        // Set application locale (this is critical for translations to work)
        app()->setLocale($locale);
        app()->instance('request_locale', $locale);

        // Store in session and cookie for persistence (mirror middleware behavior)
        session()->put('locale', $locale);
        session()->put('app.locale', $locale);
        cookie()->queue(cookie('app_locale', $locale, 60 * 24 * 30));
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
                    'reviews_count'    => Review::query()->where('is_approved', true)->count(),
                    'avg_rating'       => (float) (Review::query()->where('is_approved', true)->avg('rating') ?? 0),
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

    /**
     * @return Collection<int, Review>
     */
    #[Computed]
    public function latestReviews(): Collection
    {
        $locale = app()->getLocale();

        /** @var Collection<int, Review> $reviews */
        $reviews = TagAwareCache::remember(
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

        return $reviews;
    }

    public function addToCart(int $productId): void
    {
        $product = app(ProductRepository::class)->findPublishedById($productId);

        if (! $product || ($product->stock_quantity ?? 0) < 1) {
            $this->notifyWarning(__('product_unavailable'));

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
            'title' => __('home_homepage') . ' - ' . (is_string($appName) ? $appName : ''),
        ]);
    }
}
